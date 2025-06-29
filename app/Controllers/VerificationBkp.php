<?php
namespace App\Controllers;
use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Email\Email;

class VerificationBkp extends Controller
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

    // Dies wird nach dem Senden der Formulare ausgeführt:
    public function webhook()
    {
        log_message('debug', 'Webhook called!');
        log_message('debug', 'Webhook POST: ' . print_r($this->request->getPost(), true));

        $data = $this->request->getPost(); // Formulardaten
        $headers = array_map(function ($header) {
            return (string)$header->getValueLine();
        }, $this->request->headers());
        $referer = $this->request->getServer('HTTP_REFERER');
        log_message('debug', 'Webhook HEADERS: ' . print_r($headers, true));

        $formName = $data['form_name'] ?? null;
        unset($data['form_name']);

        $uuid = $data['uuid'] ?? null;

        // Verifizierungslogik (Beispiel: erwartet 'verified_method' im POST)
        $verifyType = $data['verified_method'] ?? null;
        $verified = in_array($verifyType, ['sms', 'phone']) ? 1 : 0;
        unset($data['verified_method']); // optional aus form_fields entfernen

        $db = \Config\Database::connect();
        $builder = $db->table('requests');

        if(!$builder->insert([
            'form_name'    => $formName,
            'form_fields'  => json_encode($data, JSON_UNESCAPED_UNICODE),
            'headers'      => json_encode(array_map(fn($h) => (string)$h, $headers), JSON_UNESCAPED_UNICODE),
            'referer'      => $referer,
            'verified'     => $verified,
            'verify_type'  => $verifyType,
            'uuid'         => $uuid,
            'created_at'   => date('Y-m-d H:i:s')
        ])) {
            log_message('error', 'Insert failed: ' . print_r($db->error(), true));
        }

        return $this->response->setJSON(['success' => true]);
    }

}
