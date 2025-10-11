<?php

namespace App\Models;

use CodeIgniter\Model;

class VerifiedPhoneModel extends Model
{
    protected $table = 'verified_phones';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'phone',
        'email',
        'verified_at',
        'verify_method',
        'platform',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Prüft, ob eine Telefonnummer bereits verifiziert wurde
     *
     * @param string $phone Normalisierte Telefonnummer (z.B. +41791234567)
     * @param string|null $email Optional: Email-Adresse für zusätzliche Prüfung
     * @param int $validityHours Gültigkeit der Verifizierung in Stunden (Standard: 24 Stunden)
     * @return bool True wenn die Nummer bereits verifiziert ist
     */
    public function isPhoneVerified(string $phone, ?string $email = null, int $validityHours = 24): bool
    {
        $builder = $this->db->table($this->table);
        $builder->where('phone', $phone);

        // Optional: Auch Email prüfen (für zusätzliche Sicherheit)
        if ($email !== null) {
            $builder->where('email', $email);
        }

        // Nur Verifizierungen innerhalb der Gültigkeitsdauer berücksichtigen
        $validFrom = date('Y-m-d H:i:s', strtotime("-{$validityHours} hours"));
        $builder->where('verified_at >=', $validFrom);

        $result = $builder->get()->getRow();

        return $result !== null;
    }

    /**
     * Speichert eine neue verifizierte Telefonnummer
     *
     * @param string $phone Normalisierte Telefonnummer
     * @param string|null $email Email-Adresse
     * @param string $verifyMethod Methode (sms oder call)
     * @param string|null $platform Platform-Identifier
     * @return int|bool Insert ID oder false bei Fehler
     */
    public function addVerifiedPhone(string $phone, ?string $email = null, string $verifyMethod = 'sms', ?string $platform = null)
    {
        // Prüfen ob Eintrag bereits existiert (innerhalb der letzten 24h)
        if ($this->isPhoneVerified($phone, $email, 24)) {
            // Update des bestehenden Eintrags
            $builder = $this->db->table($this->table);
            $builder->where('phone', $phone);

            if ($email !== null) {
                $builder->where('email', $email);
            }

            return $builder->update([
                'verified_at' => date('Y-m-d H:i:s'),
                'verify_method' => $verifyMethod,
                'platform' => $platform,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Neuen Eintrag erstellen
        return $this->insert([
            'phone' => $phone,
            'email' => $email,
            'verified_at' => date('Y-m-d H:i:s'),
            'verify_method' => $verifyMethod,
            'platform' => $platform,
        ]);
    }

    /**
     * Holt die letzte Verifizierung für eine Telefonnummer
     *
     * @param string $phone Normalisierte Telefonnummer
     * @param string|null $email Optional: Email-Adresse
     * @return array|null Verifizierungs-Daten oder null
     */
    public function getLastVerification(string $phone, ?string $email = null): ?array
    {
        $builder = $this->db->table($this->table);
        $builder->where('phone', $phone);

        if ($email !== null) {
            $builder->where('email', $email);
        }

        $builder->orderBy('verified_at', 'DESC');
        $builder->limit(1);

        $result = $builder->get()->getRow();

        return $result ? (array)$result : null;
    }

    /**
     * Löscht alte Verifizierungen (Cleanup)
     *
     * @param int $olderThanDays Lösche Einträge älter als X Tage
     * @return int Anzahl gelöschter Einträge
     */
    public function cleanupOldVerifications(int $olderThanDays = 90): int
    {
        $deleteFrom = date('Y-m-d H:i:s', strtotime("-{$olderThanDays} days"));

        return $this->where('verified_at <', $deleteFrom)->delete();
    }
}
