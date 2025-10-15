<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OfferModel;
use App\Models\VerifiedPhoneModel;

class PopulateVerifiedPhones extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:populate-verified-phones';
    protected $description = 'Populate verified_phones table with all previously verified phone numbers from offers';

    public function run(array $params)
    {
        CLI::write('Populating verified_phones table with verified phone numbers...', 'yellow');

        $offerModel = new OfferModel();
        $verifiedPhoneModel = new VerifiedPhoneModel();

        // Get all verified offers
        $verifiedOffers = $offerModel
            ->where('verified', 1)
            ->where('verify_type IS NOT', null)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        CLI::write('Found ' . count($verifiedOffers) . ' verified offers', 'blue');

        $added = 0;
        $skipped = 0;

        foreach ($verifiedOffers as $offer) {
            $formFields = json_decode($offer['form_fields'], true) ?? [];
            $phone = $formFields['phone'] ?? null;
            $email = $formFields['email'] ?? null;
            $verifyMethod = $offer['verify_type'] ?? null;
            $platform = $offer['platform'] ?? null;

            if (!$phone) {
                CLI::write("Offer ID {$offer['id']}: No phone number, skipping", 'yellow');
                $skipped++;
                continue;
            }

            // Normalize phone
            $phone = $this->normalizePhone($phone);

            // Map verify_type values
            if ($verifyMethod === 'auto_verified' || $verifyMethod === 'auto_verified_same_phone') {
                $verifyMethod = 'sms'; // Default to SMS for auto-verified
            }

            // Check if already exists
            $existing = $verifiedPhoneModel
                ->where('phone', $phone)
                ->first();

            if ($existing) {
                CLI::write("Offer ID {$offer['id']}: Phone $phone already in verified_phones, skipping", 'yellow');
                $skipped++;
                continue;
            }

            // Add to verified_phones using created_at as verified_at
            $data = [
                'phone' => $phone,
                'email' => $email,
                'verified_at' => $offer['created_at'],
                'verify_method' => ($verifyMethod === 'sms' || $verifyMethod === 'call') ? $verifyMethod : 'sms',
                'platform' => $platform,
            ];

            try {
                $verifiedPhoneModel->insert($data);
                CLI::write("Offer ID {$offer['id']}: Added phone $phone to verified_phones", 'green');
                $added++;
            } catch (\Exception $e) {
                CLI::write("Offer ID {$offer['id']}: Error adding phone $phone: " . $e->getMessage(), 'red');
                $skipped++;
            }
        }

        CLI::write("\nDone! Added $added phone numbers, skipped $skipped", 'green');
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone); // Only numbers
        if (str_starts_with($phone, '0')) {
            $phone = '+41' . substr($phone, 1); // 0781234512 → +41781234512
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }
}
