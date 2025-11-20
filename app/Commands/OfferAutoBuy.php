<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class OfferAutoBuy extends BaseCommand
{
    protected $group       = 'Offerten';
    protected $name        = 'offer:autobuy';
    protected $description = 'Kauft automatisch neue Offerten, wenn kein Sperrdatum gesetzt ist.';

    public function run(array $params)
    {
        $today = date('Y-m-d');
        $db = \Config\Database::connect();

        $userTable = $db->table('users');
        $blockedTable = $db->table('blocked_days');

        // Hole ALLE Nutzer mit aktiviertem Auto-Kauf, sortiert nach Aktivierungsdatum (Priorität!)
        $users = $userTable
            ->select('id, email_text, filter_cantons, filter_regions, filter_categories, filter_languages, filter_absences, filter_custom_zip, auto_purchase_activated_at')
            ->where('auto_purchase', 1)
            ->orderBy('auto_purchase_activated_at', 'ASC') // Früher aktiviert = höhere Priorität
            ->get()
            ->getResult();

        if (empty($users)) {
            $this->log('Keine Nutzer mit aktiviertem Auto-Kauf gefunden.', 'yellow');
            return;
        }

        $this->log('📊 Gefundene Nutzer mit Auto-Kauf: ' . count($users), 'cyan');

        // Erstelle eine Queue pro Angebot
        $offerQueues = [];

        foreach ($users as $user) {
            $isBlocked = $blockedTable
                ->where('user_id', $user->id)
                ->where('date', $today)
                ->countAllResults();

            if ($isBlocked) {
                $this->log("⏸  Benutzer #{$user->id} ({$user->email_text}) ist heute blockiert ({$today}).", 'blue');
                continue;
            }

            // Prüfe ob User gültiges Zahlungsmittel hat (Guthaben ODER Karte)
            if (!$this->hasValidPaymentMethod($user->id)) {
                $this->log("💳 Benutzer #{$user->id} hat kein gültiges Zahlungsmittel (kein Guthaben und keine Karte).", 'yellow');
                continue;
            }

            // Hole passende Offerten für diesen User
            $offers = $this->getFilteredOffersForUser($user);

            foreach ($offers as $offer) {
                // Prüfe ob User diese Offerte bereits gekauft hat
                $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
                $alreadyPurchased = $offerPurchaseModel
                    ->where('user_id', $user->id)
                    ->where('offer_id', $offer['id'])
                    ->where('status', 'paid')
                    ->countAllResults();

                if ($alreadyPurchased > 0) {
                    continue;
                }

                // Füge User zur Queue für diese Offerte hinzu
                if (!isset($offerQueues[$offer['id']])) {
                    $offerQueues[$offer['id']] = [
                        'offer' => $offer,
                        'users' => []
                    ];
                }

                $offerQueues[$offer['id']]['users'][] = $user;
            }
        }

        // Verarbeite jede Offerte mit ihrer Queue
        foreach ($offerQueues as $offerId => $data) {
            $offer = $data['offer'];
            $queuedUsers = $data['users'];

            $this->log("", 'white');
            $this->log("🎯 Verarbeite Offerte #{$offerId} - {$offer['title']}", 'cyan');
            $this->log("   Queue-Länge: " . count($queuedUsers) . " interessierte Firmen", 'cyan');

            // Prüfe wie viele Käufe bereits existieren
            $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
            $existingPurchases = $offerPurchaseModel
                ->where('offer_id', $offerId)
                ->where('status', 'paid')
                ->countAllResults();

            $remainingSlots = 3 - $existingPurchases;

            if ($remainingSlots <= 0) {
                $this->log("   ⚠️  Bereits 3 Käufe vorhanden - Offerte übersprungen", 'yellow');
                continue;
            }

            $this->log("   📋 Verfügbare Plätze: {$remainingSlots} von 3", 'green');

            // Verarbeite die ersten N User aus der Queue (nach Priorität sortiert)
            $processedCount = 0;
            foreach ($queuedUsers as $user) {
                if ($processedCount >= $remainingSlots) {
                    $this->log("   ⏹  Max. 3 Käufe erreicht - restliche Queue übersprungen", 'blue');
                    break;
                }

                $purchaseService = new \App\Services\OfferPurchaseService();
                $result = $purchaseService->purchase($user, $offerId, true);

                if ($result === true) {
                    $activatedAt = $user->auto_purchase_activated_at ?? 'unbekannt';
                    $purchaseNumber = $processedCount + 1;
                    $this->log("   ✅ Auto-Kauf #{$purchaseNumber} erfolgreich für User #{$user->id} (aktiviert: {$activatedAt})", 'green');
                    $processedCount++;
                } else {
                    // Detaillierte Fehlermeldung
                    if (is_array($result)) {
                        $this->log("   ❌ Auto-Kauf NICHT möglich für User #{$user->id}", 'red');
                        $this->log("      Guthaben: {$result['current_balance']} CHF, Benötigt: {$result['required_amount']} CHF", 'yellow');
                    } else {
                        $this->log("   ❌ Auto-Kauf NICHT möglich für User #{$user->id} - Zahlung fehlgeschlagen", 'red');
                    }
                }
            }
        }

        $this->log('', 'white');
        $this->log('✅ Auto-Buy-Verarbeitung abgeschlossen.', 'green');
    }

    /**
     * Prüft ob User ein gültiges Zahlungsmittel hat (Guthaben ODER gespeicherte Karte)
     */
    protected function hasValidPaymentMethod(int $userId): bool
    {
        // Prüfe Guthaben
        $bookingModel = new \App\Models\BookingModel();
        $balance = $bookingModel->getUserBalance($userId);

        if ($balance > 0) {
            return true;
        }

        // Prüfe gespeicherte Karte (nutzt getBestAvailableCard für Primary/Secondary Fallback)
        $paymentMethodModel = new \App\Models\UserPaymentMethodModel();
        $bestCard = $paymentMethodModel->getBestAvailableCard($userId);

        return $bestCard !== null;
    }

    protected function getFilteredOffersForUser($user): array
    {
        $offerModel = new \App\Models\OfferModel();
        $builder = $offerModel->builder();
        $builder->where('verified', 1);
        $builder->where('status', 'available');
        $builder->where('price >', 0); // Nur Offerten mit gültigem Preis

        $cantons = is_string($user->filter_cantons) ? array_filter(explode(',', $user->filter_cantons)) : $user->filter_cantons ?? [];
        $regions = is_string($user->filter_regions) ? array_filter(explode(',', $user->filter_regions)) : $user->filter_regions ?? [];
        $categories = is_string($user->filter_categories) ? array_filter(explode(',', $user->filter_categories)) : $user->filter_categories ?? [];
        $languages = is_string($user->filter_languages) ? json_decode($user->filter_languages, true) ?? [] : $user->filter_languages ?? [];
        $services = is_string($user->filter_absences) ? json_decode($user->filter_absences, true) ?? [] : $user->filter_absences ?? [];
        $customZips = is_string($user->filter_custom_zip) ? array_filter(explode(',', $user->filter_custom_zip)) : [];

        $this->log("Debug - Categories: " . implode(', ', $categories), 'yellow');

        $zipcodeService = new \App\Libraries\ZipcodeService();
        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;
        $relevantZips = $zipcodeService->getZipsByCantonAndRegion($cantons, $regions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $customZips));

        if (!empty($allZips)) {
            $builder->groupStart();
            $builder->whereIn('zip', $allZips);
            $builder->groupEnd();
        }

        if (!empty($categories)) {
            $builder->groupStart();
            foreach ($categories as $type) {
                $builder->orWhere('type', trim($type));
            }
            $builder->groupEnd();
        }

        if (!empty($languages)) {
            foreach ($languages as $lang) {
                $builder->like('language', trim($lang));
            }
        }

        if (!empty($services)) {
            foreach ($services as $service) {
                $builder->like('services', trim($service));
            }
        }

        // Noch nicht gekaufte (optional hier oder im Aufrufer prüfen)
        return $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
    }

    protected function log(string $message, string $color = 'white')
    {
        $timestamp = date('[Y-m-d H:i:s]');
        CLI::write("{$timestamp} {$message}", $color);

        // Optional in Logdatei schreiben
        file_put_contents(WRITEPATH . 'logs/offer_autobuy.log', "{$timestamp} {$message}\n", FILE_APPEND);
    }
}
