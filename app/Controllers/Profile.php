<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

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

        $data['auto_purchase'] = $this->request->getPost('auto_purchase') ? 1 : 0;
        $data['email_notifications_enabled'] = $this->request->getPost('email_notifications_enabled') ? 1 : 0;

        // --- Passwortänderung prüfen ---
        $newPassword     = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', ['password' => lang('Profile.passwordMismatch')]);
            }

            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if (!$userModel->update($user->id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $userModel->errors());
        }

        return redirect()->to('/profile')->with('success', lang('General.successMessageUpdate'));
    }
}
