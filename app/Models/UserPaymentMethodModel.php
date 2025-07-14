<?php
namespace App\Models;

use CodeIgniter\Model;
use Config\Encryption;

class UserPaymentMethodModel extends Model
{
    protected $table = 'user_payment_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'payment_method_code', 'provider_data', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    protected $encryption;

    public function __construct()
    {
        parent::__construct();
        $this->encryption = \Config\Services::encrypter();
    }

    public function saveEncryptedToken(int $userId, string $methodCode, string $token): bool
    {
        $encrypted = base64_encode($this->encryption->encrypt($token));

        return $this->insert([
            'user_id' => $userId,
            'payment_method_code' => $methodCode,
            'provider_data' => json_encode(['token' => $encrypted]),
        ]);
    }

    public function getDecryptedToken(int $userId, string $methodCode): ?string
    {
        $method = $this->where('user_id', $userId)
            ->where('payment_method_code', $methodCode)
            ->first();

        if (!$method) return null;

        $data = json_decode($method['provider_data'], true);
        if (!isset($data['token'])) return null;

        return $this->encryption->decrypt(base64_decode($data['token']));
    }
}
