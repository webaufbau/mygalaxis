<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Models\UserModel;
use App\Models\BlockedDayModel;
use App\Entities\User;
use App\Libraries\ZipcodeService;

class SendDailyNewOffers extends BaseCommand
{
    protected $group       = 'Notifications';
    protected $name        = 'offers:send-daily-new-offers';
    protected $description = 'Sendet täglich neue Offerten (von gestern) an Firmen mit passenden Filtern.';

    public function run(array $params)
    {
        $userModel = new UserModel();
        $offerModel = new OfferModel();
        $blockedModel = new BlockedDayModel();
        $zipcodeService = new ZipcodeService();

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $users = $userModel->findAll();
        $countSent = 0;

        foreach ($users as $user) {
            if(!$user->inGroup('user')) {
                continue;
            }

            // Check if user has disabled email notifications
            if (isset($user->email_notifications_enabled) && !$user->email_notifications_enabled) {
                CLI::write("📧 Benutzer #{$user->id} hat E-Mail-Benachrichtigungen deaktiviert.", 'yellow');
                continue;
            }

            if ($this->isUserBlockedToday($user->id, $today)) {
                CLI::write("⏸ Benutzer #{$user->id} blockiert heute.", 'blue');
                continue;
            }

            $offers = $this->getFilteredOffersForUser($user, $yesterday, $zipcodeService);

            if (empty($offers)) {
                continue;
            }

            $this->sendEmailToCompany($user, $offers);
            CLI::write("✅ E-Mail gesendet an {$user->getEmail()} mit " . count($offers) . " Offerten.", 'green');
            $countSent++;
        }

        CLI::write("Abgeschlossen. Total versendete E-Mails: $countSent", 'green');
    }

    protected function isUserBlockedToday(int $userId, string $today): bool
    {
        return model(BlockedDayModel::class)
                ->where('user_id', $userId)
                ->where('date', $today)
                ->countAllResults() > 0;
    }

    protected function getFilteredOffersForUser(User $user, string $date, ZipcodeService $zipcodeService): array
    {
        $offerModel = new OfferModel();
        $offerPurchaseModel = new \App\Models\OfferPurchaseModel();
        $builder = $offerModel->builder();
        $builder->where('verified', 1);
        //$builder->where('DATE(created_at)', $date);

        $cantons = is_string($user->filter_cantons) ? explode(',', $user->filter_cantons) : [];
        $regions = is_string($user->filter_regions) ? explode(',', $user->filter_regions) : [];
        $categories = is_string($user->filter_categories) ? explode(',', $user->filter_categories) : [];
        $languages = is_string($user->filter_languages) ? json_decode($user->filter_languages, true) ?? [] : [];
        $services = is_string($user->filter_absences) ? json_decode($user->filter_absences, true) ?? [] : [];
        $customZips = is_string($user->filter_custom_zip) ? explode(',', $user->filter_custom_zip) : [];

        // $zipcodeService = new \App\Libraries\ZipcodeService();
        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;
        $relevantZips = $zipcodeService->getZipsByCantonAndRegion($cantons, $regions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $customZips));

        if (!empty($allZips) && count($allZips) > 0 && !empty($allZips[0])) {
            $builder->groupStart();
            $builder->whereIn('zip', $allZips);
            $builder->groupEnd();
        }

        if (!empty($categories) && count($categories) > 0 && !empty($categories[0])) {
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

        $allOffers = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();

        // Filtere ausverkaufte Angebote (>= MAX_PURCHASES paid purchases) heraus
        $filteredOffers = [];
        foreach ($allOffers as $offer) {
            $purchaseCount = $offerPurchaseModel
                ->where('offer_id', $offer['id'])
                ->where('status', 'paid')
                ->countAllResults();

            if ($purchaseCount < \App\Models\OfferModel::MAX_PURCHASES) {
                $filteredOffers[] = $offer;
            }
        }

        return $filteredOffers;

        //dd($builder->db()->getLastQuery()->getQuery());
    }

    protected function sendEmailToCompany(User $user, array $offers): void
    {
        // Lade SiteConfig basierend auf User-Platform
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($user->platform);

        if($user->platform == 'my_offertenheld_ch') {
            //var_dump($user->platform);
            //dd($user);
        }

        // Sprache aus User-Daten setzen
        $language = $user->language ?? $offers[0]['language'] ?? 'de'; // Fallback: Deutsch
        $request = service('request');
        if ($request instanceof \CodeIgniter\HTTP\CLIRequest) {
            service('language')->setLocale($language);
        } else {
            $request->setLocale($language);
        }

        $languageService = service('language');
        $languageService->setLocale($language);

        // Lade vollständige Offerten mit data-Feld und Purchase-Status
        $offerModel = new OfferModel();
        $purchaseModel = new \App\Models\OfferPurchaseModel();
        $fullOffers = [];

        foreach ($offers as $offer) {
            $fullOffer = $offerModel->find($offer['id']);
            if ($fullOffer) {
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

                // Prüfe Purchase-Status
                $purchase = $purchaseModel
                    ->where('offer_id', $fullOffer['id'])
                    ->where('user_id', $user->id)
                    ->first();

                $fullOffer['alreadyPurchased'] = !empty($purchase);

                // Entferne sensible Adressdaten (Straße, Hausnummer) wenn noch nicht gekauft
                if (!$fullOffer['alreadyPurchased'] && !empty($fullOffer['data'])) {
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

                $fullOffers[] = $fullOffer;
            }
        }

        $data = [
            'siteConfig' => $siteConfig,
            'firma'  => $user,
            'offers' => $fullOffers,
        ];

        $subject = lang('Email.dailyOffersSubject', [$siteConfig->name]);
        $message = view('emails/daily_offer_suggestions', $data);

        $originalEmail = $user->getEmail();

        // Prüfen, ob Testmodus aktiv ist
        if ($siteConfig->testMode) {
            $emailTo = $siteConfig->testEmail;
            $subject = 'TEST EMAIL – NICHT AN ECHTEN BENUTZER! (eigentlich an: ' . $originalEmail . ') – ' . $subject;
        } else {
            $emailTo = $originalEmail;
        }

        $this->sendEmail($emailTo, $subject, $message, $siteConfig);
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

        // --- Wichtige Ergänzung: Header mit korrekter Zeitzone ---
        date_default_timezone_set('Europe/Zurich'); // falls noch nicht gesetzt
        $email->setHeader('Date', date('r')); // RFC2822-konforme aktuelle lokale Zeit

        if (!$email->send()) {
            log_message('error', 'Fehler beim Senden an ' . $to . ': ' . print_r($email->printDebugger(), true));
            return false;
        }

        return true;
    }
}
