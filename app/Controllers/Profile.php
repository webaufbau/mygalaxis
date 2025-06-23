<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Profile extends Controller
{
    public function index()
    {
        // Nur für eingeloggte Benutzer
        if (!auth()->loggedIn()) {
            return redirect()->to('/login');
        }

        $user = auth()->user();

        return view('account/profile', [
            'title' => 'Mein Konto',
            'user'  => $user,
            'errors' => session()->getFlashdata('errors'),
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
        ]);

        $data['auto_purchase'] = $this->request->getPost('auto_purchase') ? 1 : 0;


        if (!$userModel->update($user->id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $userModel->errors());
        }

        return redirect()->to('/profile')->with('success', 'Daten erfolgreich aktualisiert.');
    }
}
