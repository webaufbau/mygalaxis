<?php
namespace App\Models;

use CodeIgniter\Model;
use Config\Encryption;

class UserPaymentMethodModel extends Model
{
    protected $table = 'user_payment_methods';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'payment_method_code', 'is_primary', 'card_last4', 'card_brand', 'card_expiry', 'provider_data', 'platform', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    protected $encryption;

    public function __construct()
    {
        parent::__construct();
        // Lazy loading: Encrypter nur initialisieren wenn benötigt
        // $this->encryption wird in getEncryption() initialisiert
    }

    protected function getEncryption()
    {
        if ($this->encryption === null) {
            $this->encryption = \Config\Services::encrypter();
        }
        return $this->encryption;
    }

    public function saveEncryptedToken(int $userId, string $methodCode, string $token): bool
    {
        $encrypted = base64_encode($this->getEncryption()->encrypt($token));

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

        return $this->getEncryption()->decrypt(base64_decode($data['token']));
    }

    /**
     * Holt die primäre Zahlungsmethode eines Users
     */
    public function getPrimaryCard(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('is_primary', 1)
            ->first();
    }

    /**
     * Holt die sekundäre Zahlungsmethode (Fallback)
     */
    public function getSecondaryCard(int $userId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('is_primary', 0)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Holt alle Zahlungsmethoden eines Users sortiert (Primary zuerst)
     */
    public function getUserCards(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Setzt eine Karte als Primary und alle anderen auf Secondary
     */
    public function setPrimary(int $userId, int $cardId): bool
    {
        // Alle Karten des Users auf Secondary setzen
        $this->where('user_id', $userId)->set(['is_primary' => 0])->update();

        // Gewählte Karte auf Primary setzen
        return $this->update($cardId, ['is_primary' => 1]);
    }

    /**
     * Speichert eine neue Karte mit Kartendetails
     */
    public function saveCard(array $data): bool
    {
        // Wenn is_primary = 1, alle anderen Karten des Users auf Secondary setzen
        if (isset($data['is_primary']) && $data['is_primary'] == 1 && isset($data['user_id'])) {
            $this->where('user_id', $data['user_id'])->set(['is_primary' => 0])->update();
        }

        return $this->insert($data);
    }

    /**
     * Prüft ob eine Karte abgelaufen ist
     */
    public function isCardExpired(?string $expiryDate): bool
    {
        if (!$expiryDate) return false;

        // Format: MM/YYYY
        $parts = explode('/', $expiryDate);
        if (count($parts) !== 2) return false;

        $month = (int)$parts[0];
        $year = (int)$parts[1];

        $now = new \DateTime();
        $expiry = new \DateTime("$year-$month-01");
        $expiry->modify('last day of this month');

        return $expiry < $now;
    }

    /**
     * Holt die beste verfügbare Karte (Primary wenn nicht abgelaufen, sonst Secondary)
     * Fallback-Logik für Zahlungen
     */
    public function getBestAvailableCard(int $userId): ?array
    {
        $primary = $this->getPrimaryCard($userId);

        // Primary vorhanden und nicht abgelaufen? → verwenden
        if ($primary && !$this->isCardExpired($primary['card_expiry'])) {
            return $primary;
        }

        // Primary abgelaufen oder nicht vorhanden → Secondary holen
        $secondary = $this->getSecondaryCard($userId);

        if ($secondary && !$this->isCardExpired($secondary['card_expiry'])) {
            return $secondary;
        }

        // Keine gültige Karte gefunden
        return null;
    }
}
