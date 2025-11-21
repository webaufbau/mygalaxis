<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\CreditModel;

class Offer extends BaseController
{
    protected $creditModel;
    protected $companyModel;

    public function __construct()
    {
        $this->creditModel = new CreditModel();
        $this->companyModel = new CompanyModel();
    }

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

        // Anzahl Verkäufe ermitteln
        $purchaseModel = new \App\Models\OfferPurchaseModel();
        $purchaseCount = $purchaseModel->where('offer_id', $offer['id'])->countAllResults();

        // Käufer-Informationen holen
        $bookingModel = new \App\Models\BookingModel();
        $purchases = $bookingModel
            ->select('bookings.*, users.username, users.contact_person, users.company_name')
            ->join('users', 'users.id = bookings.user_id')
            ->where('bookings.type', 'offer_purchase')
            ->where('bookings.reference_id', $offer['id'])
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
        $sentCount = $notificationSender->notifyMatchingUsers($offer);

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

            // Verwende das gleiche Template-System wie bei normaler Verifizierung
            $success = sendOfferNotificationWithTemplate($offer, $formFields, $offer['type']);

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

}
