<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerateAffiliateCodes extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'affiliate:generate';
    protected $description = 'Generate unique affiliate codes for all users without one';

    public function run(array $params)
    {
        $userModel = new \App\Models\UserModel();

        // Get all users without affiliate code
        $users = $userModel->where('affiliate_code', null)->orWhere('affiliate_code', '')->findAll();

        if (empty($users)) {
            CLI::write('All users already have affiliate codes!', 'green');
            return;
        }

        CLI::write('Found ' . count($users) . ' users without affiliate codes.', 'yellow');
        CLI::write('Generating codes...', 'yellow');

        $generated = 0;
        $failed = 0;

        $db = \Config\Database::connect();

        foreach ($users as $user) {
            $code = $this->generateUniqueCode($userModel, $user->id);

            if ($code) {
                // Use direct DB query to avoid Shield validation issues
                $db->table('users')->where('id', $user->id)->update(['affiliate_code' => $code]);
                $generated++;
                CLI::write("User #{$user->id} ({$user->username}): {$code}", 'green');
            } else {
                $failed++;
                CLI::write("Failed to generate code for User #{$user->id}", 'red');
            }
        }

        CLI::newLine();
        CLI::write("✓ Successfully generated {$generated} affiliate codes", 'green');

        if ($failed > 0) {
            CLI::write("✗ Failed: {$failed}", 'red');
        }
    }

    /**
     * Generate a unique affiliate code for a user
     *
     * @param $userModel
     * @param int $userId
     * @return string|null
     */
    private function generateUniqueCode($userModel, int $userId): ?string
    {
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            // Generate code: Format REF-XXXXX (5 alphanumeric characters)
            $code = 'REF-' . strtoupper(substr(md5(uniqid($userId, true)), 0, 5));

            // Check if unique
            $exists = $userModel->where('affiliate_code', $code)->first();

            if (!$exists) {
                return $code;
            }
        }

        return null; // Failed after max attempts
    }
}
