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
                $this->log("⏸  Benutzer #{$user->id} ({$user->email}) ist heute blockiert ({$today}).", 'blue');
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

        foreach ($offers as $offer) {
            $alreadyPurchased = $bookingModel
                ->where('user_id', $user->id)
                ->where('type', 'offer_purchase')
                ->where('reference_id', $offer['id'])
                ->countAllResults();

            if ($alreadyPurchased > 0) {
                continue;
            }

            if ($purchaseService->purchase($user, $offer['id'], true)) {
                $this->log("✅ Auto-Kauf erfolgreich für Angebot #{$offer['id']} (Benutzer #{$user->id})", 'green');
            } else {
                $this->log("❌ Auto-Kauf NICHT möglich für Angebot #{$offer['id']} (Benutzer #{$user->id})", 'red');
            }
        }
    }

    protected function getFilteredOffersForUser($user): array
    {
        $offerModel = new \App\Models\OfferModel();
        $builder = $offerModel->builder();
        $builder->where('verified', 1);

        $cantons = is_string($user->filter_cantons) ? explode(',', $user->filter_cantons) : $user->filter_cantons ?? [];
        $regions = is_string($user->filter_regions) ? explode(',', $user->filter_regions) : $user->filter_regions ?? [];
        $categories = is_string($user->filter_categories) ? explode(',', $user->filter_categories) : $user->filter_categories ?? [];
        $languages = is_string($user->filter_languages) ? json_decode($user->filter_languages, true) ?? [] : $user->filter_languages ?? [];
        $services = is_string($user->filter_absences) ? json_decode($user->filter_absences, true) ?? [] : $user->filter_absences ?? [];
        $customZips = is_string($user->filter_custom_zip) ? explode(',', $user->filter_custom_zip) : [];

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
