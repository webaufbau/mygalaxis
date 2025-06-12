<?php
namespace App\Controllers;
use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Email\Email;

class Verification extends Controller
{
    public function sendCode()
    {
        $phone = $this->request->getPost('phone');
        if (!$phone) {
            return $this->response->setJSON(['success' => false, 'message' => 'Telefonnummer fehlt']);
        }

        $code = rand(100000, 999999);
        session()->set('verify_code', $code);
        session()->set('verify_phone', $phone);

        // SMS senden über smsup.ch
        //$apiKey = 'DEIN_API_KEY';
        //$response = file_get_contents("https://www.smsup.ch/send_sms.php?key=$apiKey&recipient=$phone&text=Ihr+Code:+$code");

        return $this->response->setJSON(['success' => true, 'data' => $this->request->getPost()]);
    }

    public function checkCode()
    {
        $entered = $this->request->getPost('code');
        $real = session()->get('verify_code');

        return $this->response->setJSON(['success' => $entered == $real]);
    }

    public function webhook()
    {
        $data = $this->request->getPost(); // kommt von FluentForm Webhook

        // 1. Daten in DB speichern
        $db = \Config\Database::connect();
        $builder = $db->table('requests'); // Tabelle muss existieren

        $builder->insert([
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'nachricht' => $data['message'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 2. E-Mail versenden
        $email = \Config\Services::email();
        $email->setTo('info@webagentur-forster.ch');
        $email->setCC('info@webaufbau.ch');
        $email->setSubject('Neue Anfrage eingegangen');
        $email->setMessage(view('emails/anfrage', ['data' => $data])); // E-Mail-Template optional

        $email->send();

        return $this->response->setJSON(['success' => true]);
    }
}
