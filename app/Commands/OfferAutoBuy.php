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

        $users = $userTable
            ->select('id, email_text, filter_cantons, filter_regions, filter_categories, filter_languages, filter_absences, filter_custom_zip')
            ->where('auto_purchase', 1)
            ->get()
            ->getResult();

        if (empty($users)) {
            $this->log('Keine Nutzer mit aktiviertem Auto-Kauf gefunden.', 'yellow');
            return;
        }

        foreach ($users as $user) {
            $isBlocked = $blockedTable
                ->where('user_id', $user->id)
                ->where('date', $today)
                ->countAllResults();

            if ($isBlocked) {
                $this->log("⏸  Benutzer #{$user->id} ({$user->email_text}) ist heute blockiert ({$today}).", 'blue');
                continue;
            }

            // Automatischen Kauf durchführen
            $this->handleAutoBuy($user);
        }

        $this->log('Auto-Buy-Verarbeitung abgeschlossen.', 'green');
    }

    protected function handleAutoBuy($user)
    {
        $offerModel = new \App\Models\OfferModel();
        $bookingModel = new \App\Models\BookingModel();
        $purchaseService = new \App\Services\OfferPurchaseService();

        // Hole gefilterte Offerten
        $offers = $this->getFilteredOffersForUser($user);

        $this->log("📋 Gefundene Offerten für User #{$user->id}: " . count($offers), 'cyan');

        foreach ($offers as $offer) {
            // Prüfe ob bereits in offer_purchases gekauft (neue Tabelle)
            $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
            $alreadyPurchased = $offerPurchaseModel
                ->where('user_id', $user->id)
                ->where('offer_id', $offer['id'])
                ->where('status', 'paid')
                ->countAllResults();

            if ($alreadyPurchased > 0) {
                continue;
            }

            $result = $purchaseService->purchase($user, $offer['id'], true);

            if ($result === true) {
                $this->log("✅ Auto-Kauf erfolgreich für Angebot #{$offer['id']} (Benutzer #{$user->id})", 'green');
            } else {
                // Detaillierte Fehlermeldung
                if (is_array($result)) {
                    $this->log("❌ Auto-Kauf NICHT möglich für Angebot #{$offer['id']} (Benutzer #{$user->id})", 'red');
                    $this->log("   Guthaben: {$result['current_balance']} CHF, Benötigt: {$result['required_amount']} CHF, Fehlt: {$result['missing_amount']} CHF", 'yellow');
                } else {
                    $this->log("❌ Auto-Kauf NICHT möglich für Angebot #{$offer['id']} (Benutzer #{$user->id}) - Angebot nicht verfügbar oder Kreditkartenzahlung fehlgeschlagen", 'red');
                }
            }
        }
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
