<?php

namespace App\Libraries;

use App\Models\UserModel;
use App\Entities\User;
use App\Libraries\ZipcodeService;

class OfferNotificationSender
{
    protected UserModel $userModel;
    protected ZipcodeService $zipcodeService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->zipcodeService = new ZipcodeService();
    }

    /**
     * E-Mail an alle passenden Firmen senden
     * @return int Anzahl versendeter E-Mails
     */
    public function notifyMatchingUsers(array $offer): int
    {
        $users = $this->userModel->findAll();
        $today = date('Y-m-d');
        $sentCount = 0;

        foreach ($users as $user) {
            if (!$user->inGroup('user')) continue;

            // Check if user has disabled email notifications
            if (isset($user->email_notifications_enabled) && !$user->email_notifications_enabled) {
                continue;
            }

            // Prüfe ob User heute blockiert ist (Agenda/Abwesenheit)
            if ($this->isUserBlockedToday($user->id, $today)) {
                continue;
            }

            if ($this->doesOfferMatchUser($offer, $user)) {
                $this->sendOfferEmail($user, $offer);
                $sentCount++;
            }
        }

        // Setze companies_notified_at wenn mindestens eine E-Mail gesendet wurde
        // ODER wenn keine passenden Firmen gefunden wurden (dann trotzdem als "verarbeitet" markieren)
        $db = \Config\Database::connect();
        $db->table('offers')->where('id', $offer['id'])->update([
            'companies_notified_at' => date('Y-m-d H:i:s')
        ]);

        log_message('info', "Firmen-Benachrichtigung für Offer ID {$offer['id']}: {$sentCount} E-Mails versendet");

        return $sentCount;
    }

    /**
     * Prüft ob ein User heute blockiert ist (Agenda-Eintrag)
     */
    protected function isUserBlockedToday(int $userId, string $today): bool
    {
        $blockedModel = model(\App\Models\BlockedDayModel::class);
        return $blockedModel
            ->where('user_id', $userId)
            ->where('date', $today)
            ->countAllResults() > 0;
    }

    protected function doesOfferMatchUser(array $offer, User $user): bool
    {
        // Prüfe erst ob User und Offer auf gleicher Platform sind
        if (!empty($offer['platform']) && !empty($user->platform)) {
            if ($offer['platform'] !== $user->platform) {
                return false; // Unterschiedliche Platforms → kein Match
            }
        }

        $cantons = is_string($user->filter_cantons) ? explode(',', $user->filter_cantons) : [];
        $regions = is_string($user->filter_regions) ? explode(',', $user->filter_regions) : [];
        $categories = is_string($user->filter_categories) ? explode(',', $user->filter_categories) : [];
        $languages = is_string($user->filter_languages) ? json_decode($user->filter_languages, true) ?? [] : [];
        $customZips = is_string($user->filter_custom_zip) ? explode(',', $user->filter_custom_zip) : [];

        // Verwende Platform der Offerte für siteConfig
        $offerPlatform = $offer['platform'] ?? null;
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($offerPlatform);
        $siteCountry = $siteConfig->siteCountry ?? null;

        $relevantZips = $this->zipcodeService->getZipsByCantonAndRegion($cantons, $regions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $customZips));

        if (!empty($allZips) && !in_array($offer['zip'], $allZips)) return false;
        if (!empty($categories) && !in_array($offer['type'], $categories)) return false;
        if (!empty($languages) && !in_array($offer['language'], $languages)) return false;

        return true;
    }

    /**
     * Extrahiert alle Felder als separate Variablen für E-Mail-Templates
     * @param array $offer Vollständige Offertendaten
     * @return array Assoziatives Array mit extrahierten Feldern
     */
    protected function extractFieldsForTemplate(array $offer): array
    {
        $fields = [];
        $db = \Config\Database::connect();

        // Grundlegende Felder aus offers-Tabelle
        $fields['city'] = $offer['city'] ?? null;
        $fields['zip'] = $offer['zip'] ?? null;
        $fields['country'] = $offer['country'] ?? null;

        // Dekodiere form_fields falls noch nicht geschehen
        $formData = $offer['data'] ?? [];
        if (is_string($formData)) {
            $formData = json_decode($formData, true) ?? [];
        }

        // Extrahiere Adress-Objekte und mache einzelne Felder verfügbar
        // Normale Adresse
        if (isset($formData['address']) && is_array($formData['address'])) {
            $fields['address_street'] = $formData['address']['address_line_1'] ?? null;
            $fields['address_number'] = $formData['address']['address_line_2'] ?? null;
            $fields['address_zip'] = $formData['address']['zip'] ?? null;
            $fields['address_city'] = $formData['address']['city'] ?? null;
        }

        // Auszug-Adresse (Umzug)
        if (isset($formData['auszug_adresse']) && is_array($formData['auszug_adresse'])) {
            $fields['auszug_street'] = $formData['auszug_adresse']['address_line_1'] ?? null;
            $fields['auszug_number'] = $formData['auszug_adresse']['address_line_2'] ?? null;
            $fields['auszug_zip'] = $formData['auszug_adresse']['zip'] ?? null;
            $fields['auszug_city'] = $formData['auszug_adresse']['city'] ?? null;
        }

        // Einzug-Adresse (Umzug)
        if (isset($formData['einzug_adresse']) && is_array($formData['einzug_adresse'])) {
            $fields['einzug_street'] = $formData['einzug_adresse']['address_line_1'] ?? null;
            $fields['einzug_number'] = $formData['einzug_adresse']['address_line_2'] ?? null;
            $fields['einzug_zip'] = $formData['einzug_adresse']['zip'] ?? null;
            $fields['einzug_city'] = $formData['einzug_adresse']['city'] ?? null;
        }

        // Auszug-Adresse Firma (Umzug Firma)
        if (isset($formData['auszug_adresse_firma']) && is_array($formData['auszug_adresse_firma'])) {
            $fields['auszug_street'] = $formData['auszug_adresse_firma']['address_line_1'] ?? null;
            $fields['auszug_number'] = $formData['auszug_adresse_firma']['address_line_2'] ?? null;
            $fields['auszug_zip'] = $formData['auszug_adresse_firma']['zip'] ?? null;
            $fields['auszug_city'] = $formData['auszug_adresse_firma']['city'] ?? null;
        }

        // Einzug-Adresse Firma (Umzug Firma)
        if (isset($formData['einzug_adresse_firma']) && is_array($formData['einzug_adresse_firma'])) {
            $fields['einzug_street'] = $formData['einzug_adresse_firma']['address_line_1'] ?? null;
            $fields['einzug_number'] = $formData['einzug_adresse_firma']['address_line_2'] ?? null;
            $fields['einzug_zip'] = $formData['einzug_adresse_firma']['zip'] ?? null;
            $fields['einzug_city'] = $formData['einzug_adresse_firma']['city'] ?? null;
        }

        // Lade Umzug-spezifische Felder aus offers_move Tabelle
        if ($offer['type'] === 'move') {
            $moveData = $db->table('offers_move')
                ->where('offer_id', $offer['id'])
                ->get()
                ->getRowArray();

            if ($moveData) {
                $fields['from_city'] = $moveData['from_city'] ?? null;
                $fields['to_city'] = $moveData['to_city'] ?? null;
                $fields['from_object_type'] = $moveData['from_object_type'] ?? null;
                $fields['to_object_type'] = $moveData['to_object_type'] ?? null;
                $fields['from_room_count'] = $moveData['from_room_count'] ?? null;
                $fields['to_room_count'] = $moveData['to_room_count'] ?? null;
                $fields['move_date'] = $moveData['move_date'] ?? null;
                $fields['customer_type'] = $moveData['customer_type'] ?? null;
            }
        }

        // Lade Umzug-Reinigung-Kombi-spezifische Felder aus offers_move_cleaning Tabelle
        if ($offer['type'] === 'move_cleaning') {
            $moveCleaningData = $db->table('offers_move_cleaning')
                ->where('offer_id', $offer['id'])
                ->get()
                ->getRowArray();

            if ($moveCleaningData) {
                $fields['from_city'] = $moveCleaningData['from_city'] ?? null;
                $fields['to_city'] = $moveCleaningData['to_city'] ?? null;
                $fields['address_city'] = $moveCleaningData['address_city'] ?? null;
                $fields['from_object_type'] = $moveCleaningData['from_object_type'] ?? null;
                $fields['to_object_type'] = $moveCleaningData['to_object_type'] ?? null;
                $fields['from_room_count'] = $moveCleaningData['from_room_count'] ?? null;
                $fields['to_room_count'] = $moveCleaningData['to_room_count'] ?? null;
                $fields['cleaning_type'] = $moveCleaningData['cleaning_type'] ?? null;
                $fields['move_date'] = $moveCleaningData['move_date'] ?? null;
                $fields['customer_type'] = $moveCleaningData['customer_type'] ?? null;
            }
        }

        // Weitere wichtige Felder aus form_fields
        $simpleFields = [
            'vorname', 'nachname', 'email', 'phone', 'telefon', 'mobile', 'handy',
            'company', 'firma', 'datetime_1', 'work_start_date', 'move_date',
            'erreichbar', 'details_hinweise', 'sonstige_hinweise'
        ];

        foreach ($simpleFields as $fieldName) {
            if (isset($formData[$fieldName]) && !is_array($formData[$fieldName])) {
                $fields[$fieldName] = $formData[$fieldName];
            }
        }

        return $fields;
    }

    protected function sendOfferEmail(User $user, array $offer): void
    {
        // Lade SiteConfig basierend auf User-Platform
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($user->platform);

        // Lade vollständige Offertendaten inkl. data-Feld
        $offerModel = new \App\Models\OfferModel();
        $fullOffer = $offerModel->find($offer['id']);

        if (!$fullOffer) {
            log_message('error', 'Offerte ID ' . $offer['id'] . ' nicht gefunden für E-Mail-Versand');
            return;
        }

        // Dekodiere data-Feld falls JSON, oder verwende form_fields als Fallback
        if (isset($fullOffer['data']) && is_string($fullOffer['data'])) {
            $fullOffer['data'] = json_decode($fullOffer['data'], true) ?? [];
        } elseif (!isset($fullOffer['data']) || empty($fullOffer['data'])) {
            // Fallback: Verwende form_fields wenn data nicht existiert
            if (isset($fullOffer['form_fields']) && is_string($fullOffer['form_fields'])) {
                $fullOffer['data'] = json_decode($fullOffer['form_fields'], true) ?? [];
            } else {
                $fullOffer['data'] = [];
            }
        }

        // Prüfe ob User diese Offerte bereits gekauft hat
        $purchaseModel = new \App\Models\OfferPurchaseModel();
        $purchase = $purchaseModel
            ->where('offer_id', $offer['id'])
            ->where('user_id', $user->id)
            ->first();

        $alreadyPurchased = !empty($purchase);

        // Entferne sensible Adressdaten (Straße, Hausnummer) wenn noch nicht gekauft
        if (!$alreadyPurchased && !empty($fullOffer['data'])) {
            foreach ($fullOffer['data'] as $key => $value) {
                if (preg_match('/adresse|address/i', $key) && is_array($value)) {
                    // Entferne address_line_1 und address_line_2, aber behalte zip und city
                    if (isset($fullOffer['data'][$key]['address_line_1'])) {
                        unset($fullOffer['data'][$key]['address_line_1']);
                    }
                    if (isset($fullOffer['data'][$key]['address_line_2'])) {
                        unset($fullOffer['data'][$key]['address_line_2']);
                    }
                }
            }
        }

        // Extrahiere separate Felder für E-Mail-Template
        $extractedFields = $this->extractFieldsForTemplate($fullOffer);

        $subject = "Neue passende Offerte #{$offer['id']}";
        $message = view('emails/offer_new_detailed', [
            'firma' => $user,
            'offer' => $fullOffer,
            'siteConfig' => $siteConfig,
            'alreadyPurchased' => $alreadyPurchased,
            'fields' => $extractedFields,
        ]);

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'   => 'Neue passende Offerten',
            'content' => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        $email = \Config\Services::email();
        $email->setTo($siteConfig->testMode ? $siteConfig->testEmail : $user->getEmail());
        $email->setFrom($siteConfig->email, $siteConfig->name);
        $email->setSubject($subject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        // --- Wichtige Ergänzung: Header mit korrekter Zeitzone ---
        date_default_timezone_set('Europe/Zurich'); // falls noch nicht gesetzt
        $email->setHeader('Date', date('r')); // RFC2822-konforme aktuelle lokale Zeit

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden an ' . $user->getEmail() . ': ' . print_r($email->printDebugger(), true));
        }
    }
}
