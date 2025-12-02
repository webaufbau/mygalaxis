<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Offer extends BaseController
{
    public function detail($id)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Angebot nicht gefunden.");
        }

        // Preisberechnung
        $calculator = new \App\Libraries\OfferPriceCalculator();
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);
        $formFieldsCombo = json_decode($offer['form_fields_combo'] ?? '{}', true);

        $calculatedPrice = $calculator->calculatePrice(
            $offer['type'] ?? '',
            $offer['original_type'] ?? '',
            $formFields,
            $formFieldsCombo
        );

        // Debug-Informationen holen
        $priceDebugInfo = $calculator->getDebugInfo();

        // Preiskomponenten holen
        $priceComponents = $calculator->getPriceComponents();

        // Maximalpreis-Cap Informationen holen
        $maxPriceCapInfo = $calculator->getMaxPriceCapInfo();

        // Rabatt berechnen
        $createdAt = \CodeIgniter\I18n\Time::parse($offer['created_at'], 'UTC')->setTimezone(app_timezone());
        $now = \CodeIgniter\I18n\Time::now(app_timezone());
        $hoursDiff = $createdAt->diff($now)->h + ($createdAt->diff($now)->days * 24);
        $discountedPrice = $calculator->applyDiscount($calculatedPrice, $hoursDiff);

        // Rabatt-Prozentsatz berechnen
        $discountPercent = 0;
        if ($discountedPrice < $calculatedPrice) {
            $discountPercent = round((($calculatedPrice - $discountedPrice) / $calculatedPrice) * 100);
        }

        // Anzahl Verkäufe ermitteln (nur aktive, nicht stornierte)
        $purchaseModel = new \App\Models\OfferPurchaseModel();
        $purchaseCount = $purchaseModel
            ->where('offer_id', $offer['id'])
            ->where('status !=', 'refunded')
            ->countAllResults();

        // Käufer-Informationen holen (nur aktive, nicht stornierte)
        $bookingModel = new \App\Models\BookingModel();
        $purchases = $bookingModel
            ->select('bookings.*, users.username, users.contact_person, users.company_name, offer_purchases.status as purchase_status')
            ->join('users', 'users.id = bookings.user_id')
            ->join('offer_purchases', 'offer_purchases.user_id = bookings.user_id AND offer_purchases.offer_id = bookings.reference_id', 'left')
            ->where('bookings.type', 'offer_purchase')
            ->where('bookings.reference_id', $offer['id'])
            ->groupStart()
                ->where('offer_purchases.status IS NULL')
                ->orWhere('offer_purchases.status !=', 'refunded')
            ->groupEnd()
            ->orderBy('bookings.created_at', 'DESC')
            ->findAll();

        // Berechnungsdetails sammeln
        $calculationDetails = $this->getCalculationDetails($offer, $formFields, $formFieldsCombo, $calculatedPrice);

        // SMS-Verifizierungs-Historie laden (mit Admin-User Info)
        $smsHistoryModel = new \App\Models\SmsVerificationHistoryModel();
        $db = \Config\Database::connect();
        $smsHistory = $db->table('sms_verification_history')
            ->select('sms_verification_history.*, users.username as admin_username')
            ->join('users', 'users.id = sms_verification_history.admin_user_id', 'left')
            ->where('sms_verification_history.offer_id', $offer['id'])
            ->orderBy('sms_verification_history.created_at', 'DESC')
            ->get()
            ->getResultArray();

        // E-Mail-Log laden (mit Firmen-Info)
        $emailLogModel = new \App\Models\OfferEmailLogModel();
        $emailLog = $emailLogModel->getEmailsWithCompanyInfo($offer['id']);

        $data['offer'] = $offer;
        $data['calculatedPrice'] = $calculatedPrice;
        $data['discountedPrice'] = $discountedPrice;
        $data['discountPercent'] = $discountPercent;
        $data['hoursDiff'] = $hoursDiff;
        $data['purchaseCount'] = $purchaseCount;
        $data['purchases'] = $purchases;
        $data['calculationDetails'] = $calculationDetails;
        $data['formFields'] = $formFields;
        $data['formFieldsCombo'] = $formFieldsCombo;
        $data['priceDebugInfo'] = $priceDebugInfo;
        $data['priceComponents'] = $priceComponents;
        $data['maxPriceCapInfo'] = $maxPriceCapInfo;
        $data['smsHistory'] = $smsHistory;
        $data['emailLog'] = $emailLog;

        return view('admin/offer_detail', $data);
    }

    private function getCalculationDetails($offer, $formFields, $formFieldsCombo, $price)
    {
        $details = [];
        $details['type'] = $offer['type'];
        $details['original_type'] = $offer['original_type'];
        $details['price'] = $price;

        switch ($offer['type']) {
            case 'cleaning':
                if (in_array($offer['original_type'], ['reinigung_nur_fenster', 'reinigung_fassaden', 'reinigung_hauswartung', 'reinigung_andere'])) {
                    $details['base'] = $offer['original_type'];
                } else {
                    $details['wohnung_groesse'] = $formFields['wohnung_groesse'] ?? null;
                    $details['komplett_anzahlzimmer'] = $formFields['komplett_anzahlzimmer'] ?? null;
                    $details['wiederkehrend'] = $formFields['reinigungsart_wiederkehrend'] ?? null;
                    $details['fensterreinigung'] = $formFields['fensterreinigung'] ?? null;
                    $details['aussenfassade'] = $formFields['aussenfassade'] ?? null;
                }
                break;

            case 'move':
            case 'move_cleaning':
                $details['auszug_zimmer'] = $formFields['auszug_zimmer'] ?? null;
                $details['auszug_arbeitsplatz_firma'] = $formFields['auszug_arbeitsplatz_firma'] ?? null;
                $details['auszug_flaeche_firma'] = $formFields['auszug_flaeche_firma'] ?? null;
                break;
        }

        return $details;
    }

    /**
     * Manuell eine Anfrage freigeben und an Firmen weiterleiten
     */
    public function manualVerify($id)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            return redirect()->back()->with('error', 'Angebot nicht gefunden.');
        }

        // Prüfe ob bereits weitergeleitet
        if ($offer['companies_notified_at']) {
            return redirect()->back()->with('info', 'Diese Anfrage wurde bereits an Firmen weitergeleitet.');
        }

        try {
            // Markiere als verifiziert (manuell)
            $offerModel->update($id, [
                'verified' => 1,
                'verify_type' => 'manual'
            ]);

            // Lade Offer neu damit verified=1 gesetzt ist
            $offer = $offerModel->find($id);

            log_message('info', "Offer nach Update neu geladen - verified={$offer['verified']}, verify_type={$offer['verify_type']}");

            // Speichere in SMS-Verifizierungs-Historie
            $adminUser = auth()->user();
            $historyModel = new \App\Models\SmsVerificationHistoryModel();
            $historyId = $historyModel->insert([
                'offer_id' => $offer['id'],
                'uuid' => $offer['uuid'],
                'phone' => $offer['phone'],
                'verification_code' => 'MANUAL',
                'method' => 'sms',
                'status' => 'MANUAL_ADMIN_APPROVAL',
                'platform' => $offer['platform'],
                'admin_user_id' => $adminUser->id ?? null,
                'verified' => 1,
                'verified_at' => date('Y-m-d H:i:s'),
            ]);

            log_message('info', "Manuelle Freigabe in SMS-Historie gespeichert (ID: $historyId) durch Admin User ID: " . ($adminUser->id ?? 'unknown'));

            // Sende E-Mails an Firmen (mit aktualisiertem Offer)
            $sentCount = $this->sendToCompanies($offer);

            // Sende Bestätigungs-E-Mail an Kunden
            $this->sendConfirmationToCustomer($offer);

            log_message('info', "Anfrage ID $id wurde manuell vom Admin freigegeben - {$sentCount} Firmen benachrichtigt, Kunde informiert");

            return redirect()->back()->with('success', "Anfrage wurde erfolgreich freigegeben! {$sentCount} Firmen wurden benachrichtigt und der Kunde hat eine Bestätigung erhalten.");

        } catch (\Exception $e) {
            log_message('error', "Fehler bei manueller Freigabe von Anfrage ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Fehler bei der Freigabe: ' . $e->getMessage());
        }
    }

    /**
     * Sendet die Anfrage an alle passenden Firmen
     */
    private function sendToCompanies($offer)
    {
        $notificationSender = new \App\Libraries\OfferNotificationSender();
        // skipManualReviewCheck = true weil Admin explizit freigibt
        $sentCount = $notificationSender->notifyMatchingUsers($offer, skipManualReviewCheck: true);

        log_message('info', "Manuell freigegebene Anfrage ID {$offer['id']}: {$sentCount} E-Mails an Firmen versendet");

        // Logge Firmen-Benachrichtigung in Email-Log (gruppiert)
        if ($sentCount > 0) {
            $emailLogModel = new \App\Models\OfferEmailLogModel();
            $priceFormatted = number_format($offer['discounted_price'] ?? $offer['price'], 0, '.', '\'');
            $typeMapping = [
                'move' => 'Umzug',
                'cleaning' => 'Reinigung',
                'move_cleaning' => 'Umzug + Reinigung',
                'painting' => 'Maler/Gipser',
            ];
            $typeName = $typeMapping[$offer['type']] ?? $offer['type'];
            $subject = "Neue Anfrage Preis Fr. {$priceFormatted}.– für {$typeName} ID {$offer['id']} - {$offer['zip']} {$offer['city']}";

            $emailLogModel->logEmail(
                offerId: $offer['id'],
                emailType: 'company_notification',
                recipientEmail: "{$sentCount} Firmen",
                recipientType: 'company',
                companyId: null,
                subject: $subject . " - an {$sentCount} Firmen versendet",
                status: 'sent'
            );
        }

        return $sentCount;
    }

    /**
     * Sendet Bestätigungs-E-Mail an den Kunden
     * Bei manuellem Review: Sendet "Approved" E-Mail (Anfrage wurde freigegeben)
     */
    private function sendConfirmationToCustomer($offer)
    {
        try {
            helper('email_template');

            // Hole form_fields für E-Mail-Adresse
            $formFields = json_decode($offer['form_fields'], true);
            $customerEmail = $formFields['email'] ?? null;

            if (!$customerEmail) {
                log_message('warning', "Keine E-Mail-Adresse für Kunde bei Anfrage ID {$offer['id']} gefunden");
                return false;
            }

            // Prüfe ob manuelle Prüfung aktiviert ist
            $platform = $offer['platform'] ?? null;
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($platform);

            if (!empty($siteConfig->manualOfferReviewEnabled)) {
                // Bei manueller Freigabe: Sende "Approved" E-Mail
                $success = sendOfferApprovedEmail($offer, $formFields, $offer['type']);
            } else {
                // Normal: Verwende das gleiche Template-System wie bei normaler Verifizierung
                $success = sendOfferNotificationWithTemplate($offer, $formFields, $offer['type']);
            }

            if ($success) {
                log_message('info', "Bestätigungs-E-Mail an Kunde {$customerEmail} gesendet (Anfrage ID {$offer['id']})");

                // Logge Bestätigungs-E-Mail in Email-Log
                $emailLogModel = new \App\Models\OfferEmailLogModel();
                $emailLogModel->logEmail(
                    offerId: $offer['id'],
                    emailType: 'confirmation',
                    recipientEmail: $customerEmail,
                    recipientType: 'customer',
                    companyId: null,
                    subject: lang('Email.offer_added_email_subject'),
                    status: 'sent'
                );

                return true;
            } else {
                log_message('warning', "Template-basierte E-Mail konnte nicht gesendet werden, verwende Fallback");
                // Fallback: Verwende direkten E-Mail-Versand
                return $this->sendConfirmationEmailFallback($offer, $formFields, $customerEmail);
            }

        } catch (\Exception $e) {
            log_message('error', "Exception beim Senden der Kunden-E-Mail für Anfrage ID {$offer['id']}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fallback für Bestätigungs-E-Mail wenn Template nicht funktioniert
     */
    private function sendConfirmationEmailFallback($offer, $formFields, $customerEmail)
    {
        try {
            $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($offer['platform'] ?? null);

            $emailService = service('email');
            $emailService->setFrom($siteConfig->email, $siteConfig->name);
            $emailService->setTo($customerEmail);
            $emailService->setSubject(lang('Email.offer_added_email_subject'));

            $message = view('emails/offer_notification', [
                'formName' => $offer['type'],
                'uuid' => $offer['uuid'],
                'verifyType' => $offer['verify_type'],
                'filteredFields' => $formFields,
                'data' => $formFields,
            ]);

            $view = \Config\Services::renderer();
            $fullEmail = $view->setData([
                'title' => 'Ihre Anfrage',
                'content' => $message,
                'siteConfig' => $siteConfig,
            ])->render('emails/layout');

            $emailService->setMessage($fullEmail);
            $emailService->setMailType('html');

            date_default_timezone_set('Europe/Zurich');
            $emailService->setHeader('Date', date('r'));

            if ($emailService->send()) {
                log_message('info', "Fallback-E-Mail an Kunde {$customerEmail} gesendet (Anfrage ID {$offer['id']})");

                // Setze confirmation_sent_at
                $db = \Config\Database::connect();
                $db->table('offers')->where('id', $offer['id'])->update([
                    'confirmation_sent_at' => date('Y-m-d H:i:s')
                ]);

                // Logge Bestätigungs-E-Mail in Email-Log
                $emailLogModel = new \App\Models\OfferEmailLogModel();
                $emailLogModel->logEmail(
                    offerId: $offer['id'],
                    emailType: 'confirmation',
                    recipientEmail: $customerEmail,
                    recipientType: 'customer',
                    companyId: null,
                    subject: lang('Email.offer_added_email_subject'),
                    status: 'sent'
                );

                return true;
            } else {
                log_message('error', "Fehler beim Senden der Fallback-E-Mail an {$customerEmail}: " . $emailService->printDebugger());
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', "Exception in Fallback-E-Mail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Zeigt alle Anfragen an, die auf manuelle Prüfung warten
     * (verifiziert = 1, aber companies_notified_at = NULL)
     */
    public function pendingReview()
    {
        $offerModel = new \App\Models\OfferModel();

        // Pagination
        $perPage = 10;
        $page = (int)($this->request->getGet('page') ?? 1);

        // Hole alle verifizierten Anfragen, die noch nicht an Firmen weitergeleitet wurden
        $pendingOffers = $offerModel
            ->where('verified', 1)
            ->where('companies_notified_at IS NULL')
            ->where('status', 'available')
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);

        $pager = $offerModel->pager;

        // Type-Mapping für Anzeige
        $typeMapping = [
            'move' => 'Umzug',
            'cleaning' => 'Reinigung',
            'move_cleaning' => 'Umzug + Reinigung',
            'painting' => 'Maler/Gipser',
            'gardening' => 'Garten',
            'electrician' => 'Elektriker',
            'plumbing' => 'Sanitär',
            'heating' => 'Heizung',
            'tiling' => 'Platten',
            'flooring' => 'Boden',
        ];

        // Enriche Daten für Anzeige
        foreach ($pendingOffers as &$offer) {
            $formFields = json_decode($offer['form_fields'] ?? '{}', true);
            $offer['customer_name'] = trim(($formFields['vorname'] ?? '') . ' ' . ($formFields['nachname'] ?? ''));
            $offer['customer_email'] = $formFields['email'] ?? '-';
            $offer['customer_phone'] = $formFields['phone'] ?? $offer['phone'] ?? '-';
            $offer['type_display'] = $typeMapping[$offer['type']] ?? ucfirst($offer['type']);
            // Ensure new fields exist (even if null)
            $offer['admin_notes'] = $offer['admin_notes'] ?? null;
            $offer['customer_hint'] = $offer['customer_hint'] ?? null;
            $offer['custom_price'] = $offer['custom_price'] ?? null;
            $offer['is_test'] = $offer['is_test'] ?? 0;

            // Berechne Zeit seit Erstellung
            $createdAt = \CodeIgniter\I18n\Time::parse($offer['created_at'], 'UTC')->setTimezone(app_timezone());
            $now = \CodeIgniter\I18n\Time::now(app_timezone());
            $diff = $createdAt->diff($now);

            if ($diff->days > 0) {
                $offer['age'] = $diff->days . ' Tag(e)';
            } elseif ($diff->h > 0) {
                $offer['age'] = $diff->h . ' Stunde(n)';
            } else {
                $offer['age'] = $diff->i . ' Minute(n)';
            }
        }

        // Gesamtzahl für Anzeige
        $totalPending = $offerModel
            ->where('verified', 1)
            ->where('companies_notified_at IS NULL')
            ->where('status', 'available')
            ->countAllResults(false);

        return view('admin/offers_pending_review', [
            'pendingOffers' => $pendingOffers,
            'pager' => $pager,
            'totalPending' => $totalPending,
            'siteConfig' => $this->siteConfig,
        ]);
    }

    /**
     * Bearbeitungsseite für eine einzelne Anfrage
     */
    public function editOffer($id)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            return redirect()->to('/admin/offers/pending')->with('error', 'Anfrage nicht gefunden.');
        }

        // Formularfelder dekodieren
        $formFields = json_decode($offer['form_fields'] ?? '{}', true);

        // Type-Mapping
        $typeMapping = [
            'move' => 'Umzug',
            'cleaning' => 'Reinigung',
            'move_cleaning' => 'Umzug + Reinigung',
            'painting' => 'Maler/Gipser',
            'gardening' => 'Garten',
            'electrician' => 'Elektriker',
            'plumbing' => 'Sanitär',
            'heating' => 'Heizung',
            'tiling' => 'Platten',
            'flooring' => 'Boden',
        ];

        // Original-Typen pro Hauptkategorie
        $originalTypes = [
            'move' => [
                'umzug_privat' => 'Privat-Umzug',
                'umzug_firma' => 'Firmen-Umzug',
            ],
            'cleaning' => [
                'reinigung_wohnung' => 'Wohnungsreinigung',
                'reinigung_haus' => 'Hausreinigung',
                'reinigung_gewerbe' => 'Gewerbereinigung',
                'reinigung_nur_fenster' => 'Nur Fensterreinigung',
                'reinigung_fassaden' => 'Fassadenreinigung',
                'reinigung_hauswartung' => 'Hauswartung',
                'reinigung_andere' => 'Andere Reinigung',
            ],
            'painting' => [
                'maler_wohnung' => 'Wohnung',
                'maler_haus' => 'Haus',
                'maler_gewerbe' => 'Gewerbe',
                'maler_andere' => 'Andere',
            ],
            'gardening' => [
                'garten_allgemeine_gartenpflege' => 'Allgemeine Gartenpflege',
                'garten_garten_umgestalten' => 'Garten umgestalten',
                'garten_neue_gartenanlage' => 'Neue Gartenanlage',
                'garten_andere_gartenarbeiten' => 'Andere Gartenarbeiten',
            ],
            'electrician' => [
                'elektriker' => 'Elektriker',
            ],
            'plumbing' => [
                'sanitaer' => 'Sanitär',
            ],
            'heating' => [
                'heizung' => 'Heizung',
            ],
            'tiling' => [
                'plattenleger' => 'Plattenleger',
            ],
            'flooring' => [
                'bodenleger' => 'Bodenleger',
            ],
        ];

        // Bearbeiter-Name holen (falls bearbeitet)
        $editedByUser = null;
        if (!empty($offer['edited_by'])) {
            $userModel = new \App\Models\UserModel();
            $editor = $userModel->find($offer['edited_by']);
            $editedByUser = $editor['username'] ?? $editor['contact_person'] ?? 'Admin';
        }

        // Audit-Log für diese Offerte laden
        $auditLogModel = new \App\Models\AuditLogModel();
        $auditLogs = $auditLogModel->getLogsForEntity('offer', (int)$id, 20);

        // User-Namen für Audit-Logs laden
        $userModel = new \App\Models\UserModel();
        foreach ($auditLogs as &$log) {
            if (!empty($log['user_id'])) {
                $logUser = $userModel->find($log['user_id']);
                $log['user_name'] = $logUser['username'] ?? $logUser['contact_person'] ?? 'Admin';
            } else {
                $log['user_name'] = 'System';
            }
        }

        return view('admin/offer_edit', [
            'offer' => $offer,
            'formFields' => $formFields,
            'typeMapping' => $typeMapping,
            'originalTypes' => $originalTypes,
            'editedByUser' => $editedByUser,
            'auditLogs' => $auditLogs,
            'siteConfig' => $this->siteConfig,
        ]);
    }

    /**
     * Aktualisiert eine Anfrage (Preis, Notizen, Test-Flag, Formularfelder etc.)
     */
    public function updateOffer($id)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            return redirect()->back()->with('error', 'Anfrage nicht gefunden.');
        }

        // Bereits an Firmen weitergeleitet?
        $action = $this->request->getPost('action');
        if ($offer['companies_notified_at'] && $action === 'save_and_send') {
            return redirect()->back()->with('error', 'Diese Anfrage wurde bereits freigegeben.');
        }

        $updateData = [];

        // Typ/Branche ändern
        $newType = $this->request->getPost('type');
        $newOriginalType = $this->request->getPost('original_type');
        if ($newType && $newType !== $offer['type']) {
            $updateData['type'] = $newType;
        }
        if ($newOriginalType && $newOriginalType !== $offer['original_type']) {
            $updateData['original_type'] = $newOriginalType;
        }

        // Custom Price
        $customPrice = $this->request->getPost('custom_price');
        if ($customPrice !== null && $customPrice !== '') {
            $updateData['custom_price'] = (float)$customPrice;
            $updateData['discounted_price'] = (float)$customPrice;
        } elseif ($customPrice === '') {
            $updateData['custom_price'] = null;
        }

        // Admin Notes (interne Notizen)
        $adminNotes = $this->request->getPost('admin_notes');
        if ($adminNotes !== null) {
            $updateData['admin_notes'] = $adminNotes;
        }

        // Customer Hint (Hinweis für Kunde)
        $customerHint = $this->request->getPost('customer_hint');
        if ($customerHint !== null) {
            $updateData['customer_hint'] = $customerHint;
        }

        // Test-Anfrage Flag
        $isTest = $this->request->getPost('is_test');
        $updateData['is_test'] = $isTest ? 1 : 0;

        // Ort-Felder direkt in offers Tabelle
        $zip = $this->request->getPost('zip');
        $city = $this->request->getPost('city');
        if ($zip !== null) {
            $updateData['zip'] = $zip;
        }
        if ($city !== null) {
            $updateData['city'] = $city;
        }

        // Selected Companies (JSON Array von User-IDs)
        $selectedCompanies = $this->request->getPost('selected_companies');
        if ($selectedCompanies !== null) {
            // Validiere JSON
            $decoded = json_decode($selectedCompanies, true);
            if (is_array($decoded)) {
                $updateData['selected_companies'] = $selectedCompanies;
            }
        }

        // Formularfelder aktualisieren
        $formFieldUpdates = $this->request->getPost('form_fields');
        if (!empty($formFieldUpdates) && is_array($formFieldUpdates)) {
            $currentFormFields = json_decode($offer['form_fields'] ?? '{}', true);

            foreach ($formFieldUpdates as $key => $value) {
                // Adress-Felder speziell behandeln
                if ($key === 'address' && is_array($value)) {
                    if (!isset($currentFormFields['address'])) {
                        $currentFormFields['address'] = [];
                    }
                    $currentFormFields['address'] = array_merge($currentFormFields['address'], $value);
                } else {
                    $currentFormFields[$key] = $value;
                }
            }

            $updateData['form_fields'] = json_encode($currentFormFields, JSON_UNESCAPED_UNICODE);

            // Auch Kontaktdaten in Haupttabelle aktualisieren falls geändert
            if (isset($formFieldUpdates['vorname'])) {
                $updateData['firstname'] = $formFieldUpdates['vorname'];
            }
            if (isset($formFieldUpdates['nachname'])) {
                $updateData['lastname'] = $formFieldUpdates['nachname'];
            }
            if (isset($formFieldUpdates['email'])) {
                $updateData['email'] = $formFieldUpdates['email'];
            }
            if (isset($formFieldUpdates['phone'])) {
                $updateData['phone'] = $formFieldUpdates['phone'];
            }
        }

        // Edit-Tracking: Bearbeitungszeitpunkt und -user setzen
        $adminUser = auth()->user();
        $updateData['edited_at'] = date('Y-m-d H:i:s');
        $updateData['edited_by'] = $adminUser->id ?? null;

        if (!empty($updateData)) {
            // Audit-Log: Änderungen protokollieren (ohne technische Felder)
            $auditOldValues = [];
            $auditNewValues = [];
            $excludeFromAudit = ['edited_at', 'edited_by', 'form_fields', 'selected_companies'];

            foreach ($updateData as $key => $newValue) {
                if (in_array($key, $excludeFromAudit)) continue;
                $oldValue = $offer[$key] ?? null;
                if ((string)$newValue !== (string)$oldValue) {
                    $auditOldValues[$key] = $oldValue;
                    $auditNewValues[$key] = $newValue;
                }
            }

            if (!empty($auditNewValues)) {
                \App\Models\AuditLogModel::log(
                    'offer_updated',
                    'offer',
                    (int)$id,
                    $auditOldValues,
                    $auditNewValues
                );
            }

            $offerModel->update($id, $updateData);
            log_message('info', "Anfrage ID $id aktualisiert von User {$adminUser->id}: " . json_encode(array_keys($updateData)));
        }

        // Aktion: Nur speichern oder Speichern & Senden?
        if ($action === 'save_and_send') {
            // Lade Offer neu mit Änderungen
            $offer = $offerModel->find($id);

            try {
                // Speichere in SMS-Verifizierungs-Historie
                $historyModel = new \App\Models\SmsVerificationHistoryModel();
                $historyModel->insert([
                    'offer_id' => $offer['id'],
                    'uuid' => $offer['uuid'],
                    'phone' => $offer['phone'],
                    'verification_code' => 'APPROVED',
                    'method' => 'admin',
                    'status' => 'ADMIN_APPROVED',
                    'platform' => $offer['platform'],
                    'admin_user_id' => $adminUser->id ?? null,
                    'verified' => 1,
                    'verified_at' => date('Y-m-d H:i:s'),
                ]);

                // Approved-Tracking setzen
                $offerModel->update($id, [
                    'approved_at' => date('Y-m-d H:i:s'),
                    'approved_by' => $adminUser->id ?? null,
                ]);

                // Sende an ausgewählte Firmen (oder alle passenden)
                $sentCount = $this->sendToSelectedCompanies($offer);

                // Sende Bestätigungs-E-Mail an Kunden (Approved E-Mail)
                $this->sendConfirmationToCustomer($offer);

                // Audit-Log: Freigabe protokollieren
                \App\Models\AuditLogModel::log(
                    'offer_approved',
                    'offer',
                    (int)$id,
                    null,
                    ['sent_to_companies' => $sentCount]
                );

                log_message('info', "Anfrage ID $id wurde vom Admin freigegeben - {$sentCount} Firmen benachrichtigt");

                return redirect()->to('/admin/offers/pending')->with('success', "Anfrage #{$id} wurde freigegeben und an {$sentCount} Firmen gesendet!");

            } catch (\Exception $e) {
                log_message('error', "Fehler bei Freigabe von Anfrage ID $id: " . $e->getMessage());
                return redirect()->back()->with('error', 'Fehler bei der Freigabe: ' . $e->getMessage());
            }
        }

        // Nur speichern - zurück zur Edit-Seite
        return redirect()->to("/admin/offers/edit/{$id}")->with('success', "Anfrage #{$id} wurde gespeichert.");
    }

    /**
     * Sendet die Anfrage an ausgewählte Firmen (oder alle passenden falls keine ausgewählt)
     */
    private function sendToSelectedCompanies($offer)
    {
        $selectedCompanyIds = json_decode($offer['selected_companies'] ?? '[]', true);

        // Wenn spezifische Firmen ausgewählt wurden
        if (!empty($selectedCompanyIds) && is_array($selectedCompanyIds)) {
            $notificationSender = new \App\Libraries\OfferNotificationSender();
            $sentCount = $notificationSender->notifySpecificUsers($offer, $selectedCompanyIds, skipManualReviewCheck: true);

            log_message('info', "Anfrage ID {$offer['id']}: {$sentCount} E-Mails an ausgewählte Firmen versendet (IDs: " . implode(',', $selectedCompanyIds) . ")");

            // Logge in Email-Log
            if ($sentCount > 0) {
                $emailLogModel = new \App\Models\OfferEmailLogModel();
                $priceFormatted = number_format($offer['custom_price'] ?? $offer['discounted_price'] ?? $offer['price'], 0, '.', '\'');
                $typeMapping = [
                    'move' => 'Umzug',
                    'cleaning' => 'Reinigung',
                    'move_cleaning' => 'Umzug + Reinigung',
                    'painting' => 'Maler/Gipser',
                ];
                $typeName = $typeMapping[$offer['type']] ?? $offer['type'];
                $subject = "Neue Anfrage Preis Fr. {$priceFormatted}.– für {$typeName} ID {$offer['id']} - {$offer['zip']} {$offer['city']}";

                $emailLogModel->logEmail(
                    offerId: $offer['id'],
                    emailType: 'company_notification',
                    recipientEmail: "{$sentCount} ausgewählte Firmen",
                    recipientType: 'company',
                    companyId: null,
                    subject: $subject,
                    status: 'sent'
                );
            }

            return $sentCount;
        }

        // Fallback: Alle passenden Firmen (wie bisher)
        return $this->sendToCompanies($offer);
    }

    /**
     * Sucht Firmen nach PLZ und Umkreis (AJAX)
     */
    public function searchCompanies()
    {
        $zipcode = $this->request->getGet('zipcode');
        $radius = (int)$this->request->getGet('radius') ?: 20;
        $category = $this->request->getGet('category'); // Optional: Kategorie/Branche filtern
        $blocked = $this->request->getGet('blocked'); // Optional: 'active', 'blocked', oder leer für alle

        if (empty($zipcode)) {
            return $this->response->setJSON(['error' => 'PLZ erforderlich']);
        }

        // Hole alle PLZ im Umkreis
        $zipcodeService = new \App\Libraries\ZipcodeService();
        $nearbyZipcodes = $zipcodeService->getZipcodesInRadius($zipcode, $radius);

        log_message('debug', "[searchCompanies] PLZ: {$zipcode}, Radius: {$radius}, Found zipcodes: " . count($nearbyZipcodes));

        if (empty($nearbyZipcodes)) {
            return $this->response->setJSON(['error' => 'PLZ nicht gefunden', 'companies' => [], 'debug' => "No zipcodes found for {$zipcode}"]);
        }

        $zipList = array_column($nearbyZipcodes, 'zipcode');
        log_message('debug', "[searchCompanies] Ziplist sample: " . implode(',', array_slice($zipList, 0, 10)));

        // Suche Firmen in diesen PLZ
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();

        $builder = $db->table('users')
            ->select('users.id, users.company_name, users.contact_person, users.company_email as email, users.company_phone as phone, users.company_zip as zip, users.company_city as city, users.is_test, users.is_blocked, users.filter_categories')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'user')
            ->whereIn('users.company_zip', $zipList)
            ->orderBy('users.company_name', 'ASC');

        // Kategorie-Filter auf DB-Ebene wenn angegeben
        if (!empty($category)) {
            // filter_categories ist ein Komma-separierter String, z.B. "move,cleaning,painting"
            $builder->like('users.filter_categories', $category);
        }

        // Blockiert-Filter
        if ($blocked === 'active') {
            $builder->where('users.is_blocked', 0);
        } elseif ($blocked === 'blocked') {
            $builder->where('users.is_blocked', 1);
        }

        $companies = $builder->get()->getResultArray();

        log_message('debug', "[searchCompanies] Companies found before filter: " . count($companies));

        // Füge Distanz-Info hinzu und prüfe Kategorie genauer
        $zipDistances = [];
        foreach ($nearbyZipcodes as $z) {
            $zipDistances[$z['zipcode']] = round((float)$z['distance_km'], 1);
        }

        $filteredCompanies = [];
        foreach ($companies as &$company) {
            $company['distance_km'] = $zipDistances[$company['zip']] ?? null;

            // Nochmals genaue Prüfung der Kategorie (LIKE kann falsche Matches liefern)
            // Wenn Kategorie-Filter gesetzt: nur Firmen mit dieser Kategorie zeigen
            // Wenn Firma keine filter_categories hat: zeige sie trotzdem (keine Einschränkung)
            if (!empty($category) && !empty($company['filter_categories'])) {
                $userCategories = explode(',', $company['filter_categories']);
                if (!in_array($category, $userCategories)) {
                    continue; // Diese Firma bietet die Kategorie nicht an
                }
            }

            // Entferne filter_categories aus Antwort (nicht nötig für Frontend)
            unset($company['filter_categories']);
            $filteredCompanies[] = $company;
        }

        // Sortiere nach Distanz
        usort($filteredCompanies, fn($a, $b) => ($a['distance_km'] ?? 999) <=> ($b['distance_km'] ?? 999));

        return $this->response->setJSON([
            'zipcode' => $zipcode,
            'radius' => $radius,
            'category' => $category,
            'total_zipcodes' => count($nearbyZipcodes),
            'companies' => $filteredCompanies,
        ]);
    }

    /**
     * Genehmigt eine Anfrage und sendet sie an Firmen + Kunde
     */
    public function approveOffer($id)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            return redirect()->back()->with('error', 'Anfrage nicht gefunden.');
        }

        // Prüfe ob bereits weitergeleitet
        if ($offer['companies_notified_at']) {
            return redirect()->back()->with('info', 'Diese Anfrage wurde bereits freigegeben.');
        }

        try {
            // Speichere in SMS-Verifizierungs-Historie
            $adminUser = auth()->user();
            $historyModel = new \App\Models\SmsVerificationHistoryModel();
            $historyModel->insert([
                'offer_id' => $offer['id'],
                'uuid' => $offer['uuid'],
                'phone' => $offer['phone'],
                'verification_code' => 'APPROVED',
                'method' => 'admin',
                'status' => 'ADMIN_APPROVED',
                'platform' => $offer['platform'],
                'admin_user_id' => $adminUser->id ?? null,
                'verified' => 1,
                'verified_at' => date('Y-m-d H:i:s'),
            ]);

            // Sende E-Mails an Firmen
            $sentCount = $this->sendToCompanies($offer);

            // Sende Bestätigungs-E-Mail an Kunden (Approved E-Mail)
            $this->sendConfirmationToCustomer($offer);

            log_message('info', "Anfrage ID $id wurde vom Admin freigegeben - {$sentCount} Firmen benachrichtigt");

            return redirect()->to('/admin/offers/pending')->with('success', "Anfrage #{$id} wurde erfolgreich freigegeben! {$sentCount} Firmen wurden benachrichtigt.");

        } catch (\Exception $e) {
            log_message('error', "Fehler bei Freigabe von Anfrage ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Fehler bei der Freigabe: ' . $e->getMessage());
        }
    }

}
