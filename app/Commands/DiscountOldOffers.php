<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Models\UserModel;
use App\Libraries\ZipcodeService;
use App\Entities\User;
use DateTime;

class DiscountOldOffers extends BaseCommand
{
    protected $group       = 'Offers';
    protected $name        = 'offers:discount-old';
    protected $description = 'Wendet graduellen Rabatt an (8h→30%, 14h→50%, 24h→70%). Sendet Email nur bei Preisänderung.';

    public function run(array $params)
    {
        $offerModel = new OfferModel();
        $userModel = new UserModel();
        $calculator = new \App\Libraries\OfferPriceCalculator();
        $offerPurchaseModel = new \App\Models\OfferPurchaseModel();

        // Hol alle Offers
        $offers = $offerModel
            ->where('price >', 0)
            ->findAll();

        if (empty($offers)) {
            CLI::write('Keine Offers gefunden.', 'yellow');
            return;
        }

        $updated = 0;
        $skipped = 0;
        $now = new DateTime();

        foreach ($offers as $offer) {
            // Prüfe Anzahl PAID Käufe - keine Rabatte wenn >= MAX_PURCHASES
            $purchaseCount = $offerPurchaseModel
                ->where('offer_id', $offer['id'])
                ->where('status', 'paid')
                ->countAllResults();

            if ($purchaseCount >= \App\Models\OfferModel::MAX_PURCHASES) {
                CLI::write("Offer #{$offer['id']}: Übersprungen (ausverkauft: {$purchaseCount} Verkäufe)", 'yellow');
                $skipped++;
                continue;
            }

            // Berechne Alter in Stunden
            $createdAt = new DateTime($offer['created_at']);
            $diff = $createdAt->diff($now);
            $hoursDiff = $diff->h + ($diff->days * 24);

            // Berechne neuen Rabattpreis basierend auf Regeln
            $basePrice = $offer['price'];
            $newDiscountedPrice = $calculator->applyDiscount($basePrice, $hoursDiff);

            // Wenn kein Rabatt anwendbar
            if ($newDiscountedPrice >= $basePrice) {
                // Wenn vorher ein Rabatt war, entfernen
                if ($offer['discounted_price']) {
                    $offerModel->update($offer['id'], ['discounted_price' => null]);
                    CLI::write("Offer #{$offer['id']}: Rabatt entfernt (noch zu jung)", 'blue');
                }
                $skipped++;
                continue;
            }

            // Nur updaten wenn sich Preis geändert hat
            $currentDiscountedPrice = $offer['discounted_price'] ?? $basePrice;

            if ($newDiscountedPrice != $currentDiscountedPrice) {
                // Update Offer
                $offerModel->update($offer['id'], [
                    'discounted_price' => $newDiscountedPrice,
                    'discounted_at'    => date('Y-m-d H:i:s'),
                ]);

                $discount = round(($basePrice - $newDiscountedPrice) / $basePrice * 100);
                CLI::write("Offer #{$offer['id']}: {$basePrice} → {$newDiscountedPrice} CHF ({$discount}% Rabatt, {$hoursDiff}h alt)", 'green');

                // Benachrichtige passende Firmen
                $users = $userModel->findAll();
                $today = date('Y-m-d');
                $notifiedCount = 0;
                foreach ($users as $user) {
                    if (!$user->inGroup('user')) {
                        continue;
                    }

                    // Check if user has disabled email notifications
                    if (isset($user->email_notifications_enabled) && !$user->email_notifications_enabled) {
                        continue;
                    }

                    // Prüfe ob User heute blockiert ist (Agenda/Abwesenheit)
                    if ($this->isUserBlockedToday($user->id, $today)) {
                        continue;
                    }

                    if ($this->doesOfferMatchUser($offer, $user)) {
                        // Immer Original-Preis als oldPrice verwenden, nicht den vorherigen reduzierten Preis
                        $this->sendPriceUpdateEmail($user, $offer, $basePrice, $newDiscountedPrice);
                        $notifiedCount++;
                    }
                }

                CLI::write("  → {$notifiedCount} Firma(n) benachrichtigt", 'cyan');
                $updated++;
            } else {
                $skipped++;
            }
        }

        CLI::newLine();
        CLI::write("Fertig! {$updated} Angebote aktualisiert, {$skipped} unverändert.", 'green');
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
        $cantons = is_string($user->filter_cantons) ? explode(',', $user->filter_cantons) : [];
        $regions = is_string($user->filter_regions) ? explode(',', $user->filter_regions) : [];
        $categories = is_string($user->filter_categories) ? explode(',', $user->filter_categories) : [];
        $languages = is_string($user->filter_languages) ? json_decode($user->filter_languages, true) ?? [] : [];
        $customZips = is_string($user->filter_custom_zip) ? explode(',', $user->filter_custom_zip) : [];

        $zipcodeService = new ZipcodeService();
        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;

        $relevantZips = $zipcodeService->getZipsByCantonAndRegion($cantons, $regions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $customZips));

        if (!empty($allZips) && !in_array($offer['zip'], $allZips)) {
            return false;
        }

        if (!empty($categories) && !in_array($offer['type'], $categories)) {
            return false;
        }

        if (!empty($languages) && !in_array($offer['language'], $languages)) {
            return false;
        }

        return true;
    }

    protected function sendPriceUpdateEmail(User $user, array $offer, float $oldPrice, float $newPrice): void
    {
        // Lade SiteConfig basierend auf User-Platform
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($user->platform);

        // Lade vollständige Offertendaten inkl. form_fields
        $offerModel = new OfferModel();
        $fullOffer = $offerModel->find($offer['id']);

        if (!$fullOffer) {
            CLI::error('Offerte ID ' . $offer['id'] . ' nicht gefunden für E-Mail-Versand');
            return;
        }

        // Dekodiere form_fields als data-Feld
        if (isset($fullOffer['form_fields']) && is_string($fullOffer['form_fields'])) {
            $fullOffer['data'] = json_decode($fullOffer['form_fields'], true) ?? [];
        } elseif (isset($fullOffer['data']) && is_string($fullOffer['data'])) {
            $fullOffer['data'] = json_decode($fullOffer['data'], true) ?? [];
        } else {
            $fullOffer['data'] = [];
        }

        // Prüfe ob User diese Offerte bereits gekauft hat
        $purchaseModel = new \App\Models\OfferPurchaseModel();
        $purchase = $purchaseModel
            ->where('offer_id', $offer['id'])
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->first();

        $alreadyPurchased = !empty($purchase);

        // Extrahiere Domain aus Platform
        $domain = '';
        if (!empty($offer['platform'])) {
            $domain = str_replace('my_', '', $offer['platform']);
            $domain = str_replace('_', '.', $domain);
        } else {
            // Fallback: extrahiere aus frontendUrl
            $url = $siteConfig->frontendUrl ?? base_url();
            $domain = preg_replace('#^https?://([^/]+).*$#', '$1', $url);
            $parts = explode('.', $domain);
            if (count($parts) >= 2) {
                $domain = $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
            }
        }
        // Capitalize first letter
        $domain = ucfirst($domain);

        // Typ mit spezifischen Formulierungen für E-Mail-Betreffs
        $type = $this->getOfferTypeForSubject($offer['type']);

        $discount = round(($oldPrice - $newPrice) / $oldPrice * 100);

        // Neuer Preis formatieren
        $newPriceFormatted = number_format($newPrice, 2, '.', '\'');

        // Format: "Offertenschweiz.ch - 40% Rabatt / Neuer Preis Fr. 38.00 auf Anfrage für Garten Arbeiten ID 457 4244 Röschenz"
        $subject = "{$discount}% Rabatt / Neuer Preis Fr. {$newPriceFormatted} für {$type} {$offer['zip']} {$offer['city']} ID {$offer['id']} Anfrage";

        // Versuche field_display_template aus Datenbank zu laden für bessere Formatierung
        $customFieldDisplay = null;
        $templateModel = new \App\Models\EmailTemplateModel();
        $offerModel = new \App\Models\OfferModel();
        $language = $fullOffer['data']['lang'] ?? $fullOffer['language'] ?? 'de';

        // Erkenne Subtype aus Formulardaten
        $subtype = $offerModel->detectSubtype($fullOffer['data']);

        $emailTemplate = $templateModel->getTemplateForOffer($offer['type'], $language, $subtype);

        if ($emailTemplate && !empty($emailTemplate['field_display_template'])) {
            // Parse field_display_template mit EmailTemplateParser
            $parser = new \App\Services\EmailTemplateParser($offer['platform']);

            $fieldConfigForExclusion = new \Config\FormFieldOptions();
            if ($alreadyPurchased) {
                $excludedFields = $fieldConfigForExclusion->excludedFieldsAlways;
            } else {
                $excludedFields = array_merge(
                    $fieldConfigForExclusion->excludedFieldsAlways,
                    ['vorname', 'nachname', 'email', 'phone', 'telefon', 'tel',
                     'e-mail', 'e_mail', 'mail', 'mobile', 'handy',
                     'strasse', 'street', 'address', 'adresse', 'hausnummer']
                );
            }

            $customFieldDisplay = $parser->parse($emailTemplate['field_display_template'], $fullOffer['data'], $excludedFields);
        }

        // Verwende price_update View (für Firmen-Rabatte, nicht für Kunden)
        $message = view('emails/price_update', [
            'firma' => $user,
            'offer' => $fullOffer,
            'oldPrice' => $oldPrice,
            'newPrice' => $newPrice,
            'discount' => $discount,
            'siteConfig' => $siteConfig,
            'alreadyPurchased' => $alreadyPurchased,
            'customFieldDisplay' => $customFieldDisplay, // Übergebe custom field display
        ]);

        $to = $siteConfig->testMode ? $siteConfig->testEmail : $user->getEmail();

        $this->sendEmail($to, $subject, $message, $siteConfig);
    }

    protected function sendEmail(string $to, string $subject, string $message, $siteConfig = null): bool
    {
        $siteConfig = $siteConfig ?? siteconfig();

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title'   => 'Neue passende Offerten',
            'content' => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        helper('email_template');
        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom($siteConfig->email, getEmailFromName($siteConfig));
        $email->setSubject($subject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }

    /**
     * Gibt den korrekten Typ-Namen für E-Mail-Betreffs zurück
     * Spezielle Formulierungen je nach Branche für "Neue Anfrage" E-Mails
     */
    protected function getOfferTypeForSubject(string $offerType): string
    {
        // Spezielle Formulierungen für Betreffs
        $typeMapping = [
            'move'              => 'Umzug',
            'cleaning'          => 'Reinigung',
            'move_cleaning'     => 'Umzug + Reinigung',
            'painting'          => 'Maler/Gipser',
            'painter'           => 'Maler/Gipser',
            'gardening'         => 'Garten Arbeiten',
            'gardener'          => 'Garten Arbeiten',
            'electrician'       => 'Elektriker Arbeiten',
            'plumbing'          => 'Sanitär Arbeiten',
            'heating'           => 'Heizung Arbeiten',
            'tiling'            => 'Platten Arbeiten',
            'flooring'          => 'Boden Arbeiten',
            'furniture_assembly'=> 'Möbelaufbau',
            'other'             => 'Sonstiges',
        ];

        return $typeMapping[$offerType] ?? ucfirst(str_replace('_', ' ', $offerType));
    }
}
