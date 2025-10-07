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
     */
    public function notifyMatchingUsers(array $offer): void
    {
        $users = $this->userModel->findAll();
        $today = date('Y-m-d');

        foreach ($users as $user) {
            if (!$user->inGroup('user')) continue;

            // Prüfe ob User heute blockiert ist (Agenda/Abwesenheit)
            if ($this->isUserBlockedToday($user->id, $today)) {
                continue;
            }

            if ($this->doesOfferMatchUser($offer, $user)) {
                $this->sendOfferEmail($user, $offer);
            }
        }
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

        $siteConfig = siteconfig();
        $siteCountry = $siteConfig->siteCountry ?? null;

        $relevantZips = $this->zipcodeService->getZipsByCantonAndRegion($cantons, $regions, $siteCountry);
        $allZips = array_unique(array_merge($relevantZips, $customZips));

        if (!empty($allZips) && !in_array($offer['zip'], $allZips)) return false;
        if (!empty($categories) && !in_array($offer['type'], $categories)) return false;
        if (!empty($languages) && !in_array($offer['language'], $languages)) return false;

        return true;
    }

    protected function sendOfferEmail(User $user, array $offer): void
    {
        // Lade SiteConfig basierend auf User-Platform
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($user->platform);

        $subject = "Neue passende Offerte #{$offer['id']}";
        $message = view('emails/offer_new', [
            'firma' => $user,
            'offer' => $offer,
            'siteConfig' => $siteConfig,
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
