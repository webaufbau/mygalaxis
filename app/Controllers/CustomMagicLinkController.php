<?php

namespace App\Controllers;

use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Controllers\MagicLinkController as ShieldMagicLinkController;
use App\Models\AuthIdentityModel;
use App\Models\UserModel;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Models\UserIdentityModel;

class CustomMagicLinkController extends ShieldMagicLinkController
{

    /**
     * Receives the email from the user, creates the hash
     * to a user identity, and sends an email to the given
     * email address.
     *
     * @return RedirectResponse|string
     */
    public function loginAction()
    {
        if (! setting('Auth.allowMagicLinkLogins')) {
            return redirect()->route('login')->with('error', lang('Auth.magicLinkDisabled'));
        }

        // Validate email format
        $rules = $this->getValidationRules();
        if (! $this->validateData($this->request->getPost(), $rules, [], config('Auth')->DBGroup)) {
            return redirect()->route('magic-link')->with('errors', $this->validator->getErrors());
        }

        // Check if the user exists
        $email = $this->request->getPost('email');
        $user  = $this->provider->findByCredentials(['email' => $email]);

        if ($user === null) {
            return redirect()->route('magic-link')->with('error', lang('Auth.invalidEmail', [$email]));
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // Delete any previous magic-link identities
        $identityModel->deleteIdentitiesByType($user, Session::ID_TYPE_MAGIC_LINK);

        // Generate the code and save it as an identity
        helper('text');
        $token = random_string('crypto', 20);

        $identityModel->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => $token,
            'expires' => Time::now()->addSeconds(setting('Auth.magicLinkLifetime')),
        ]);

        /** @var IncomingRequest $request */
        $request = service('request');

        $ipAddress = $request->getIPAddress();
        $userAgent = (string) $request->getUserAgent();
        $date      = Time::now()->toDateTimeString();

        // Send the user an email with the code
        helper('email');
        $email = emailer(['mailType' => 'html'])
            ->setFrom(siteconfig()->email ?? setting('Email.fromEmail'), siteconfig()->name ?? setting('Email.fromName') ?? '');
        $email->setTo($user->email);
        $email->setSubject(lang('Auth.magicLinkSubject'));
        $email->setMessage($this->view(
            setting('Auth.views')['magic-link-email'],
            ['token' => $token, 'user' => $user, 'ipAddress' => $ipAddress, 'userAgent' => $userAgent, 'date' => $date],
            ['debug' => false],
        ));

        if ($email->send(false) === false) {
            log_message('error', $email->printDebugger(['headers']));

            return redirect()->route('magic-link')->with('error', lang('Auth.unableSendEmailToUser', [$user->email]));
        }

        // Clear the email
        $email->clear();

        return $this->displayMessage();
    }
}
