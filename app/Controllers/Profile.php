<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\EmailChangeRequestModel;
use App\Libraries\CustomEmail;
use CodeIgniter\Controller;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class Profile extends Controller
{
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        return view('account/profile', [
            'title'   => 'Mein Konto',
            'user'    => $user,
            'errors'  => session()->getFlashdata('errors'),
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function update()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        $userModel = new UserModel();

        $data = $this->request->getPost([
            'company_name',
            'contact_person',
            'company_uid',
            'company_street',
            'company_zip',
            'company_city',
            'company_website',
            'company_email',
            'company_phone',
            'language',
        ]);

        $newAutoPurchaseValue = $this->request->getPost('auto_purchase') ? 1 : 0;
        $data['auto_purchase'] = $newAutoPurchaseValue;
        $data['email_notifications_enabled'] = $this->request->getPost('email_notifications_enabled') ? 1 : 0;

        // Wenn auto_purchase gerade aktiviert wird, setze das Aktivierungsdatum
        if ($newAutoPurchaseValue == 1 && empty($user->auto_purchase)) {
            $data['auto_purchase_activated_at'] = date('Y-m-d H:i:s');
        }
        // Wenn deaktiviert wird, lösche das Datum
        elseif ($newAutoPurchaseValue == 0 && !empty($user->auto_purchase)) {
            $data['auto_purchase_activated_at'] = null;
        }

        if (!$userModel->update($user->id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $userModel->errors());
        }

        return redirect()->to('/profile')->with('success', lang('General.successMessageUpdate'));
    }

    // E-Mail-Adresse ändern (Login)
    public function email()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        return view('account/email', [
            'title'   => lang('Profile.changeEmail'),
            'user'    => $user,
            'errors'  => session()->getFlashdata('errors'),
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function updateEmail()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        $newEmail = $this->request->getPost('new_email');
        $currentPassword = $this->request->getPost('current_password');

        // Validierung
        $validation = \Config\Services::validation();
        $validation->setRules([
            'new_email' => 'required|valid_email',
            'current_password' => 'required'
        ], [
            'new_email' => [
                'required' => lang('Profile.emailRequired'),
                'valid_email' => lang('Profile.emailInvalid')
            ],
            'current_password' => [
                'required' => lang('Profile.currentPasswordRequired')
            ]
        ]);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        // Prüfen, ob E-Mail bereits existiert
        $identityModel = model('CodeIgniter\Shield\Models\UserIdentityModel');
        $existingIdentity = $identityModel->where('secret', $newEmail)->first();

        if ($existingIdentity && $existingIdentity->user_id != $user->id) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['new_email' => lang('Profile.emailAlreadyExists')]);
        }

        // Aktuelles Passwort prüfen
        if (!password_verify($currentPassword, $user->password_hash)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['current_password' => lang('Profile.currentPasswordIncorrect')]);
        }

        // Token generieren
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Alte Anfragen löschen
        $emailChangeModel = new EmailChangeRequestModel();
        $emailChangeModel->deleteOldRequests($user->id);

        // Neue Anfrage speichern
        $emailChangeModel->insert([
            'user_id' => $user->id,
            'old_email' => $user->email,
            'new_email' => $newEmail,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        // Bestätigungs-E-Mail an neue Adresse senden
        $confirmUrl = base_url('profile/email/confirm/' . $token);

        try {
            $customEmail = new CustomEmail();
            $customEmail->setFrom(service('settings')->get('Email.fromEmail', 'noreply@' . $_SERVER['HTTP_HOST']),
                                  service('settings')->get('Email.fromName', 'My Galaxy'));
            $customEmail->setTo($newEmail);
            $customEmail->setSubject(lang('Profile.emailChangeConfirmSubject'));

            $emailData = [
                'user' => $user,
                'confirmUrl' => $confirmUrl,
                'expiresAt' => $expiresAt,
            ];

            $customEmail->setMessage(view('emails/email_change_confirm', $emailData));
            $customEmail->setMailType('html');

            if (!$customEmail->send()) {
                log_message('error', 'Failed to send email confirmation: ' . $customEmail->printDebugger());
            }

            // Info-E-Mail an alte Adresse senden
            $customEmail = new CustomEmail();
            $customEmail->setFrom(service('settings')->get('Email.fromEmail', 'noreply@' . $_SERVER['HTTP_HOST']),
                                  service('settings')->get('Email.fromName', 'My Galaxy'));
            $customEmail->setTo($user->email);
            $customEmail->setSubject(lang('Profile.emailChangeNotificationSubject'));

            $emailData = [
                'user' => $user,
                'newEmail' => $newEmail,
            ];

            $customEmail->setMessage(view('emails/email_change_notification', $emailData));
            $customEmail->setMailType('html');

            if (!$customEmail->send()) {
                log_message('error', 'Failed to send email notification: ' . $customEmail->printDebugger());
            }
        } catch (\Exception $e) {
            log_message('error', 'Email sending exception: ' . $e->getMessage());
        }

        return redirect()->to('/profile/email')->with('success', lang('Profile.emailChangeRequestSent'));
    }

    // E-Mail-Änderung bestätigen
    public function confirmEmail($token)
    {
        $emailChangeModel = new EmailChangeRequestModel();
        $request = $emailChangeModel->findByToken($token);

        if (!$request) {
            return redirect()->to('/profile/email')->with('errors', ['token' => lang('Profile.emailChangeTokenInvalid')]);
        }

        // E-Mail über Shield Identity aktualisieren
        $identityModel = model('CodeIgniter\Shield\Models\UserIdentityModel');
        $identity = $identityModel->where('user_id', $request->user_id)
            ->where('type', 'email_password')
            ->first();

        if ($identity) {
            $identityModel->update($identity->id, ['secret' => $request->new_email]);
        }

        // Anfrage löschen
        $emailChangeModel->delete($request->id);

        // Erfolgsmeldung
        return redirect()->to('/profile/email')->with('success', lang('Profile.emailUpdateSuccess'));
    }

    // Passwort ändern
    public function password()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        return view('account/password', [
            'title'   => lang('Profile.changePassword'),
            'user'    => $user,
            'errors'  => session()->getFlashdata('errors'),
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function updatePassword()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();
        $shieldUserModel = new ShieldUserModel();

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validierung
        $validation = \Config\Services::validation();
        $validation->setRules([
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ], [
            'current_password' => [
                'required' => lang('Profile.currentPasswordRequired')
            ],
            'new_password' => [
                'required' => lang('Profile.newPasswordRequired'),
                'min_length' => lang('Profile.passwordMinLength')
            ],
            'confirm_password' => [
                'required' => lang('Profile.confirmPasswordRequired'),
                'matches' => lang('Profile.passwordMismatch')
            ]
        ]);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()
                ->with('errors', $validation->getErrors());
        }

        // Aktuelles Passwort prüfen
        if (!password_verify($currentPassword, $user->password_hash)) {
            return redirect()->back()
                ->with('errors', ['current_password' => lang('Profile.currentPasswordIncorrect')]);
        }

        // Neues Passwort über Shield Identity aktualisieren
        $identityModel = model('CodeIgniter\Shield\Models\UserIdentityModel');
        $identity = $identityModel->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->first();

        if (!$identity) {
            return redirect()->back()
                ->with('errors', ['general' => 'Identity nicht gefunden']);
        }

        // Passwort hashen und aktualisieren
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        if (!$identityModel->update($identity->id, ['secret2' => $passwordHash])) {
            return redirect()->back()
                ->with('errors', $identityModel->errors());
        }

        return redirect()->to('/profile/password')->with('success', lang('Profile.passwordUpdateSuccess'));
    }
}
