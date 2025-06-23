<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Auth extends Controller
{
    public function login()
    {
        // Beispiel: Lade Login View
        return view('\CodeIgniter\Shield\Views\login'); // auth/login
    }

    public function processLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user->password)) {
            session()->set('user_id', $user->id);
            return redirect()->to('/dashboard');
        }

        return redirect()->back()->with('error', 'Login fehlgeschlagen');
    }

    public function forgotPassword()
    {
        helper(['form', 'url']);
        $session = session();

        if ($this->request->getMethod() === 'post') {
            $email = $this->request->getPost('email');

            // Einfaches Validieren der E-Mail
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $session->setFlashdata('error', 'Bitte gib eine gültige E-Mail-Adresse ein.');
                return redirect()->back();
            }

            // Prüfe, ob User mit dieser E-Mail existiert
            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();

            if (!$user) {
                // Sicherheitshalber: nicht sagen, dass die Mail nicht existiert
                $session->setFlashdata('success', 'Falls die E-Mail registriert ist, erhältst du einen Link zum Zurücksetzen.');
                return redirect()->to('/auth/login');
            }

            // Token generieren und speichern (z.B. 64 Zeichen zufällig)
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $userModel->update($user['id'], [
                'reset_token' => $token,
                'reset_expires' => $expires,
            ]);

            // Link zum Zurücksetzen
            $resetLink = base_url("/auth/reset-password?token=$token");

            // Mail versenden (hier einfach per mail(), besser SMTP nutzen)
            $subject = 'Passwort zurücksetzen';
            $message = "Hallo,\n\nBitte klicke auf folgenden Link, um dein Passwort zurückzusetzen:\n$resetLink\n\nDer Link ist 1 Stunde gültig.";
            $headers = 'From: no-reply@deinedomain.de';

            mail($email, $subject, $message, $headers);

            $session->setFlashdata('success', 'Falls die E-Mail registriert ist, erhältst du einen Link zum Zurücksetzen.');
            return redirect()->to('/auth/login');
        }

        echo view('auth/forgot-password');
    }

    public function processForgotPassword()
    {
        $email = $this->request->getPost('email');

        if (empty($email)) {
            return redirect()->back()->withInput()->with('error', 'Bitte gib deine E-Mail-Adresse ein.');
        }

        $user = (new UserModel())->where('email', $email)->first();

        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'Benutzer wurde nicht gefunden.');
        }

        $passwords = service('passwords');

        // Send reset link
        $passwords->forgot($user);

        return redirect()->back()->with('message', 'Wenn die E-Mail existiert, wurde ein Link zum Zurücksetzen des Passworts gesendet.');
    }


    public function resetPassword()
    {
        $session = session();
        $userModel = new UserModel();

        if ($this->request->getMethod() === 'get') {
            $token = $this->request->getGet('token');
            if (!$token) {
                return redirect()->to('/auth/login');
            }
            // Prüfe Token
            $user = $userModel->where('reset_token', $token)->first();
            if (!$user || strtotime($user['reset_expires']) < time()) {
                $session->setFlashdata('error', 'Ungültiger oder abgelaufener Link.');
                return redirect()->to('/auth/forgot-password');
            }

            echo view('auth/reset-password', ['token' => $token]);
            return;
        }

        // POST: Neues Passwort setzen
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $password_confirm = $this->request->getPost('password_confirm');

        if (!$token) {
            $session->setFlashdata('error', 'Ungültige Anfrage.');
            return redirect()->to('/auth/forgot-password');
        }

        if ($password !== $password_confirm) {
            $session->setFlashdata('error', 'Passwörter stimmen nicht überein.');
            return redirect()->back()->withInput();
        }

        // Prüfe Token
        $user = $userModel->where('reset_token', $token)->first();
        if (!$user || strtotime($user['reset_expires']) < time()) {
            $session->setFlashdata('error', 'Ungültiger oder abgelaufener Link.');
            return redirect()->to('/auth/forgot-password');
        }

        // Passwort hashen und speichern
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userModel->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_expires' => null,
        ]);

        $session->setFlashdata('success', 'Passwort wurde erfolgreich geändert. Du kannst dich jetzt anmelden.');
        return redirect()->to('/auth/login');
    }

    public function register()
    {
        $session = session();

        if ($this->request->getMethod() === 'POST') {
            $email = $this->request->getPost('email');
            $name = $this->request->getPost('name');
            $password = $this->request->getPost('password');
            $password_confirm = $this->request->getPost('password_confirm');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $session->setFlashdata('error', 'Bitte gültige E-Mail eingeben.');
                return redirect()->back()->withInput();
            }

            if ($password !== $password_confirm) {
                $session->setFlashdata('error', 'Passwörter stimmen nicht überein.');
                return redirect()->back()->withInput();
            }

            if (strlen($password) < 6) {
                $session->setFlashdata('error', 'Passwort muss mindestens 6 Zeichen lang sein.');
                return redirect()->back()->withInput();
            }

            $userModel = new UserModel();

            if ($userModel->where('email', $email)->first()) {
                $session->setFlashdata('error', 'E-Mail ist bereits registriert.');
                return redirect()->back()->withInput();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $userModel->insert([
                'email' => $email,
                'name' => $name,
                'password' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $session->setFlashdata('success', 'Registrierung erfolgreich. Du kannst dich jetzt einloggen.');
            return redirect()->to('/auth/login');
        }

        echo view('auth/register');
    }

    public function processRegister()
    {
        return $this->register(); // einfach delegieren
    }



}
