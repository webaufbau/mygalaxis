<?php

namespace App\Libraries;

use App\Entities\User;

class CompanyWelcomeMailer
{
    public function sendWelcomeEmail(User $user): bool
    {
        // Lade SiteConfig basierend auf User-Platform
        $siteConfig = \App\Libraries\SiteConfigLoader::loadForPlatform($user->platform);

        $subject = "Danke für Ihre Anmeldung als Firma";
        $message = view('emails/company_welcome', [
            'user' => $user,
            'company_name' => $user->company_name,
            'company_email' => $user->company_email,
            'siteConfig' => $siteConfig,
            'website_name' => $siteConfig->name,
            'website_email' => $siteConfig->email,
            'backend_url' => $siteConfig->backendUrl,
        ]);

        $view = \Config\Services::renderer();
        $fullEmail = $view->setData([
            'title' => 'Willkommen bei ' . $siteConfig->name,
            'content' => $message,
            'siteConfig' => $siteConfig,
        ])->render('emails/layout');

        $email = \Config\Services::email();
        $email->setTo($user->getEmail());
        $email->setFrom($siteConfig->email, $siteConfig->name);
        $email->setSubject($subject);
        $email->setMessage($fullEmail);
        $email->setMailType('html');

        date_default_timezone_set('Europe/Zurich');
        $email->setHeader('Date', date('r'));

        if (!$email->send()) {
            log_message('error', 'Willkommens-Mail konnte nicht gesendet werden: ' . print_r($email->printDebugger(), true));
            return false;
        }

        // Mark email as sent in database
        $userModel = new \App\Models\UserModel();
        $userModel->update($user->id, [
            'welcome_email_sent' => date('Y-m-d H:i:s')
        ]);

        log_message('info', 'Willkommens-Mail erfolgreich gesendet an: ' . $user->getEmail());

        return true;
    }
}
