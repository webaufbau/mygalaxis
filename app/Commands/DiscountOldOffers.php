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

        // Typ mit spezifischen Formulierungen für E-Mail-Betreffs
        $type = $this->getOfferTypeForSubject($offer['type']);

        $discount = round(($oldPrice - $newPrice) / $oldPrice * 100);

        // Format: "50% Rabatt auf Anfrage für Garten Arbeiten #457 4244 Röschenz"
        $subject = "{$discount}% Rabatt auf Anfrage für {$type} #{$offer['id']} {$offer['zip']} {$offer['city']}";

        // Lade E-Mail-Template aus Datenbank
        $templateModel = new \App\Models\EmailTemplateModel();
        $language = $fullOffer['data']['lang'] ?? $fullOffer['language'] ?? 'de';
        $emailTemplate = $templateModel->getTemplateForOffer($offer['type'], $language);

        // Wenn kein Template gefunden, fallback zum alten View
        if (!$emailTemplate) {
            $message = view('emails/price_update', [
                'firma' => $user,
                'offer' => $fullOffer,
                'oldPrice' => $oldPrice,
                'newPrice' => $newPrice,
                'discount' => $discount,
                'siteConfig' => $siteConfig,
                'alreadyPurchased' => $alreadyPurchased,
            ]);
        } else {
            // Verwende Template aus Datenbank
            $parser = new \App\Services\EmailTemplateParser($offer['platform']);

            // Lade excluded fields config
            $fieldConfigForExclusion = new \Config\FormFieldOptions();

            // Wenn bereits gekauft, zeige alle Felder (außer den Always-Excluded)
            // Wenn nicht gekauft, verstecke zusätzlich Kontaktdaten
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

            // Parse field_display_template
            $fieldDisplayHtml = '';
            if (!empty($emailTemplate['field_display_template'])) {
                $fieldDisplayHtml = $parser->parse($emailTemplate['field_display_template'], $fullOffer['data'], $excludedFields);
            } else {
                $fieldDisplayHtml = $parser->parse('[show_all]', $fullOffer['data'], $excludedFields);
            }

            // Erstelle Rabatt-Box HTML
            $rabattBox = '<div style="background-color: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin: 30px 0;">';
            $rabattBox .= '<h3 style="margin-top: 0; color: #856404;">Preisänderung</h3>';
            $rabattBox .= '<p style="margin: 10px 0;"><span style="text-decoration: line-through; color: #6c757d; font-size: 18px;">Alter Preis: ' . esc($oldPrice) . ' ' . currency($offer['platform'] ?? null) . '</span></p>';
            $rabattBox .= '<p style="margin: 10px 0;"><strong style="font-size: 24px; color: #28a745;">Neuer Preis: ' . esc($newPrice) . ' ' . currency($offer['platform'] ?? null) . '</strong></p>';
            $rabattBox .= '<p style="margin: 10px 0; color: #856404;"><strong>(' . $discount . '% Rabatt)</strong></p>';

            if ($alreadyPurchased) {
                // Kontaktdaten Box wenn bereits gekauft
                $rabattBox .= '<div style="background-color: #d4edda; border: 2px solid #28a745; border-radius: 8px; padding: 20px; margin: 20px 0;">';
                $rabattBox .= '<h4 style="color: #155724; margin-top: 0;">Kontaktdaten des Kunden</h4><ul style="list-style: none; padding: 0;">';
                if (!empty($fullOffer['data']['vorname']) || !empty($fullOffer['data']['nachname'])) {
                    $rabattBox .= '<li><strong>Name:</strong> ' . esc($fullOffer['data']['vorname'] ?? '') . ' ' . esc($fullOffer['data']['nachname'] ?? '') . '</li>';
                }
                if (!empty($fullOffer['data']['email'])) {
                    $rabattBox .= '<li><strong>E-Mail:</strong> <a href="mailto:' . esc($fullOffer['data']['email']) . '">' . esc($fullOffer['data']['email']) . '</a></li>';
                }
                $phone = $fullOffer['data']['phone'] ?? $fullOffer['data']['telefon'] ?? $fullOffer['data']['tel'] ?? '';
                if ($phone) {
                    $rabattBox .= '<li><strong>Telefon:</strong> <a href="tel:' . esc($phone) . '">' . esc($phone) . '</a></li>';
                }
                $rabattBox .= '</ul></div>';
                $rabattBox .= '<div style="text-align: center; margin-top: 20px;"><p style="margin: 0; font-size: 18px; color: #155724; font-weight: bold;">✓ Diese Anfrage wurde bereits gekauft</p></div>';
            } else {
                $rabattBox .= '<div style="text-align: center; margin-top: 20px;"><a href="' . rtrim($siteConfig->backendUrl, '/') . '/offers/buy/' . $offer['id'] . '" style="display: inline-block; background-color: #28a745; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-size: 18px; font-weight: bold;">Jetzt kaufen</a></div>';
            }
            $rabattBox .= '</div>';

            // Füge Rabatt-Box am Ende des Field-Displays hinzu
            $fieldDisplayHtml .= $rabattBox;

            // Replace {{FIELD_DISPLAY}} in body_template
            $bodyTemplate = str_replace('{{FIELD_DISPLAY}}', $fieldDisplayHtml, $emailTemplate['body_template']);

            // Parse the complete body with all shortcodes
            $message = $parser->parse($bodyTemplate, $fullOffer['data']);
        }

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

        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom($siteConfig->email, $siteConfig->name);
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
