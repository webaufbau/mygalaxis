<?php
namespace App\Models;

use CodeIgniter\Model;

class OfferModel extends Model
{
    protected $table = 'offers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'type',
        'status',
        'price',
        'buyers',
        'bought_by',
        'firstname',
        'lastname',
        'email',
        'phone',
        'additional_service',
        'service_url',
        'uuid',
        'customer_type',
        'city',
        'zip',
        'form_fields',
        'headers',
        'referer',
        'verified',
        'verify_type',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Optional: vor Insert automatisch Typ erkennen und weitere Felder extrahieren
    protected function beforeInsert(array $data): array
    {
        $fields = json_decode($data['data']['form_fields'] ?? '{}', true);
        $userInputs = $fields['__submission']['user_inputs'] ?? [];

        if (is_array($fields)) {
            $data['data']['type'] = $this->detectType($fields);
            $data['data']['city'] = $fields['auszug_adresse_firma']['city'] ?? null;
            $data['data']['zip'] = $fields['auszug_adresse_firma']['zip'] ?? null;
            $data['data']['customer_type'] = isset($fields['firmenname']) ? 'firma' : 'privat';

            $data['data']['firstname'] = $fields['vorname'] ?? $userInputs['vorname'] ?? null;
            $data['data']['lastname'] = $fields['nachname'] ?? $userInputs['nachname'] ?? null;
            $data['data']['email'] = $fields['email'] ?? $userInputs['email'] ?? null;
            $data['data']['phone'] = $fields['phone'] ?? $userInputs['phone'] ?? null;
            $data['data']['additional_service'] = $fields['additional_service'] ?? null;
            $data['data']['service_url'] = $fields['service_url'] ?? null;
            $data['data']['uuid'] = $data['data']['uuid'] ?? bin2hex(random_bytes(16));
        }

        return $data;
    }

    protected function detectType(array $fields): string
    {
        $source = $fields['service_url'] ?? '';

        if (str_contains($source, 'umzuege')) return 'move';
        if (str_contains($source, 'reinigung')) return 'cleaning';
        if (str_contains($source, 'maler')) return 'painter';
        if (str_contains($source, 'garten')) return 'gardener';
        if (str_contains($source, 'sanitaer')) return 'plumbing';

        return 'unknown';
    }
}
