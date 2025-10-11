<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Migriert alle bereits verifizierten Telefonnummern aus der offers-Tabelle
 * in die neue verified_phones-Tabelle
 *
 * Verwendung:
 * php spark migrate:verified-phones
 */
class MigrateExistingVerifications extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'migrate:verified-phones';
    protected $description = 'Migriert bereits verifizierte Telefonnummern aus offers in verified_phones';

    public function run(array $params)
    {
        CLI::write('Starte Migration von bereits verifizierten Telefonnummern...', 'yellow');

        $db = \Config\Database::connect();

        // Hole alle verifizierten Offerten
        $builder = $db->table('offers');
        $builder->select('id, form_fields, verified, verify_type, platform, created_at');
        $builder->where('verified', 1);
        $builder->orderBy('created_at', 'ASC');

        $offers = $builder->get()->getResultArray();

        if (empty($offers)) {
            CLI::write('Keine verifizierten Offerten gefunden.', 'yellow');
            return;
        }

        CLI::write('Gefunden: ' . count($offers) . ' verifizierte Offerten', 'blue');

        $verifiedPhoneModel = new \App\Models\VerifiedPhoneModel();
        $migratedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($offers as $offer) {
            try {
                $fields = json_decode($offer['form_fields'], true);

                if (!$fields) {
                    CLI::write('  [SKIP] Offer #' . $offer['id'] . ': Konnte form_fields nicht dekodieren', 'red');
                    $skippedCount++;
                    continue;
                }

                $phone = $fields['phone'] ?? null;
                $email = $fields['email'] ?? null;

                if (!$phone) {
                    CLI::write('  [SKIP] Offer #' . $offer['id'] . ': Keine Telefonnummer gefunden', 'red');
                    $skippedCount++;
                    continue;
                }

                // Telefonnummer normalisieren (wie im Verification Controller)
                $phone = $this->normalizePhone($phone);

                // Prüfe ob bereits existiert
                if ($verifiedPhoneModel->isPhoneVerified($phone, $email, 999999)) {
                    CLI::write('  [EXISTS] Offer #' . $offer['id'] . ': ' . $phone . ' bereits in verified_phones', 'yellow');
                    $skippedCount++;
                    continue;
                }

                // Speichern mit dem ursprünglichen Verifizierungszeitpunkt
                $verifyMethod = $offer['verify_type'] ?? 'sms';
                if (!in_array($verifyMethod, ['sms', 'call'])) {
                    $verifyMethod = 'sms'; // Fallback für auto_verified etc.
                }

                $insertData = [
                    'phone' => $phone,
                    'email' => $email,
                    'verified_at' => $offer['created_at'], // Original-Zeitpunkt
                    'verify_method' => $verifyMethod,
                    'platform' => $offer['platform'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $result = $verifiedPhoneModel->insert($insertData);

                if ($result) {
                    CLI::write('  [OK] Offer #' . $offer['id'] . ': ' . $phone . ' migriert', 'green');
                    $migratedCount++;
                } else {
                    CLI::write('  [ERROR] Offer #' . $offer['id'] . ': Fehler beim Speichern', 'red');
                    $errorCount++;
                }

            } catch (\Exception $e) {
                CLI::write('  [ERROR] Offer #' . $offer['id'] . ': ' . $e->getMessage(), 'red');
                $errorCount++;
            }
        }

        CLI::newLine();
        CLI::write('=== Migration abgeschlossen ===', 'yellow');
        CLI::write('Migriert: ' . $migratedCount, 'green');
        CLI::write('Übersprungen: ' . $skippedCount, 'yellow');
        CLI::write('Fehler: ' . $errorCount, 'red');
        CLI::newLine();

        // Statistik
        $totalVerifications = $verifiedPhoneModel->countAllResults();
        CLI::write('Gesamt in verified_phones: ' . $totalVerifications, 'blue');
    }

    /**
     * Normalisiert Telefonnummer (gleiche Logik wie Verification Controller)
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone); // Nur Zahlen
        if (str_starts_with($phone, '0')) {
            $phone = '+41' . substr($phone, 1); // 0781234512 → +41781234512
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }
}
