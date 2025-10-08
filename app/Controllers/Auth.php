<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Auth extends Controller
{
    public function login()
    {
        return view('\CodeIgniter\Shield\Views\login');
    }

    /**
     * Admin Login View - separate page for administrators
     */
    public function adminLoginView()
    {
        // If already logged in and is admin, redirect to admin area
        if (auth()->loggedIn()) {
            if (auth()->user()->inGroup('admin')) {
                return redirect()->to('/admin/user');
            }
            // If logged in but not admin, logout first
            auth()->logout();
        }

        return view('admin/login');
    }

    /**
     * Admin Login Attempt - handles admin login submission
     */
    public function adminLoginAttempt()
    {
        $credentials = $this->request->getPost([
            'email',
            'password',
        ]);

        $credentials = array_filter($credentials);
        $credentials['email'] = $this->request->getPost('email');
        $credentials['password'] = $this->request->getPost('password');
        $remember = (bool) $this->request->getPost('remember');

        // Attempt to login
        $result = auth()->attempt($credentials, $remember);

        if (!$result->isOK()) {
            return redirect()->route('admin-login')->with('error', $result->reason());
        }

        // Check if user is admin
        if (!auth()->user()->inGroup('admin')) {
            auth()->logout();
            return redirect()->route('admin-login')->with('error', lang('Auth.adminOnly'));
        }

        // Success - redirect to admin user list (or another existing admin page)
        return redirect()->to('/admin/user')->with('message', lang('Auth.successLogin'));
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

        return redirect()->back()->with('error', lang('Auth.loginFailed'));
    }

    public function forgotPassword()
    {
        helper(['form', 'url']);
        $session = session();

        if ($this->request->getMethod() === 'POST') {
            $email = $this->request->getPost('email');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $session->setFlashdata('error', lang('Auth.invalidEmail'));
                return redirect()->back();
            }

            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();

            if (!$user) {
                $session->setFlashdata('success', lang('Auth.resetLinkSentIfRegistered'));
                return redirect()->to('/auth/login');
            }

            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $userModel->update($user['id'], [
                'reset_token' => $token,
                'reset_expires' => $expires,
            ]);

            $resetLink = base_url("/auth/reset-password?token=$token");

            $subject = lang('Auth.resetPasswordSubject');
            $message = sprintf(
                lang('Auth.resetPasswordMessage'),
                $resetLink
            );
            $headers = 'From: no-reply@deinedomain.de';

            mail($email, $subject, $message, $headers);

            $session->setFlashdata('success', lang('Auth.resetLinkSentIfRegistered'));
            return redirect()->to('/auth/login');
        }

        echo view('auth/forgot-password');
    }

    public function processForgotPassword()
    {
        $email = $this->request->getPost('email');

        if (empty($email)) {
            return redirect()->back()->withInput()->with('error', lang('Auth.enterEmail'));
        }

        $user = (new UserModel())->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('error', lang('Auth.userNotFound'));
        }

        $passwords = service('passwords');
        $passwords->forgot($user);

        return redirect()->back()->with('message', lang('Auth.resetLinkSentIfRegistered'));
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
            $user = $userModel->where('reset_token', $token)->first();
            if (!$user || strtotime($user['reset_expires']) < time()) {
                $session->setFlashdata('error', lang('Auth.invalidOrExpiredLink'));
                return redirect()->to('/auth/forgot-password');
            }

            echo view('auth/reset-password', ['token' => $token]);
            return;
        }

        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $password_confirm = $this->request->getPost('password_confirm');

        if (!$token) {
            $session->setFlashdata('error', lang('Auth.invalidRequest'));
            return redirect()->to('/auth/forgot-password');
        }

        if ($password !== $password_confirm) {
            $session->setFlashdata('error', lang('Auth.passwordsDontMatch'));
            return redirect()->back()->withInput();
        }

        $user = $userModel->where('reset_token', $token)->first();
        if (!$user || strtotime($user['reset_expires']) < time()) {
            $session->setFlashdata('error', lang('Auth.invalidOrExpiredLink'));
            return redirect()->to('/auth/forgot-password');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userModel->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_expires' => null,
        ]);

        $session->setFlashdata('success', lang('Auth.passwordChangedSuccess'));
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
                $session->setFlashdata('error', lang('Auth.messageInvalidEmail'));
                return redirect()->back()->withInput();
            }

            if ($password !== $password_confirm) {
                $session->setFlashdata('error', lang('Auth.passwordsDontMatch'));
                return redirect()->back()->withInput();
            }

            if (strlen($password) < 6) {
                $session->setFlashdata('error', lang('Auth.passwordMinLength'));
                return redirect()->back()->withInput();
            }

            $userModel = new UserModel();

            if ($userModel->where('email', $email)->first()) {
                $session->setFlashdata('error', lang('Auth.emailAlreadyRegistered'));
                return redirect()->back()->withInput();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $userModel->insert([
                'email' => $email,
                'name' => $name,
                'password' => $hashedPassword,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $session->setFlashdata('success', lang('Auth.registrationSuccess'));
            return redirect()->to('/auth/login');
        }

        echo view('auth/register');
    }

    public function processRegister()
    {
        return $this->register();
    }
}
