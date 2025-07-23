<?php
namespace App\Models;

use CodeIgniter\Model;
use DateTime;

class OfferModel extends Model
{
    protected $table = 'offers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'type',
        'title',
        'status',
        'price',
        'buyers',
        'bought_by',
        'firstname',
        'lastname',
        'email',
        'phone',
        'work_start_date',
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
        'from_campaign',
        'created_at',
        'updated_at',
        'checked_at',
        'reminder_sent_at',
        'verification_token',
        'group_id',
        'form_fields_combo',
        'access_hash',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';


    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];

    // Optional: vor Insert automatisch Typ erkennen und weitere Felder extrahieren
    protected function beforeInsert(array $data): array
    {
        $fields = json_decode($data['data']['form_fields'] ?? '{}', true);
        $userInputs = $fields['__submission']['user_inputs'] ?? [];

        log_message('debug', 'before Insert fields ' . print_r($fields, true));
        log_message('debug', 'before Insert userInputs ' . print_r($userInputs, true));

        if (empty($data['data']['verification_token'])) {
            $data['data']['verification_token'] = bin2hex(random_bytes(32)); // 64 Zeichen Hex
        }

        return $data;
    }

    public function enrichDataFromFormFields(array $formFields, array $original = []): array
    {
        $userInputs = $formFields['__submission']['user_inputs'] ?? [];

        // Adresse extrahieren
        $address = $this->extractAddressData($formFields);

        $data = [];
        if(!$original || !isset($original['type']) || !str_contains($original['type'], '_')) {
            $data['type'] = $this->detectType($formFields);
        }
        $data['city'] = $address['city'];
        $data['zip'] = $address['zip'];
        $data['customer_type'] = isset($formFields['firmenname']) ? 'firma' : 'privat';

        $data['firstname'] = $formFields['vorname'] ?? $userInputs['vorname'] ?? null;
        $data['lastname'] = $formFields['nachname'] ?? $userInputs['nachname'] ?? null;
        $data['email'] = $formFields['email'] ?? $userInputs['email'] ?? null;
        $data['phone'] = $formFields['phone'] ?? $userInputs['phone'] ?? null;
        $data['additional_service'] = $formFields['additional_service'] ?? null;
        $data['service_url'] = $formFields['service_url'] ?? null;

        $date = DateTime::createFromFormat('d/m/Y', $formFields['datetime_1']);
        $timestamp = $date ? $date->getTimestamp() : false;
        $data['work_start_date'] = date("Y-m-d", $timestamp) ?? null;

        if (empty($original['uuid'])) {
            $data['uuid'] = bin2hex(random_bytes(16));
        }

        return $data;
    }

    protected function detectType(array $fields): string
    {
        $source =
            $fields['_wp_http_referer']
            ?? $fields['__submission']['source_url']
            ?? $fields['service_url']
            ?? '';

        if (str_contains($source, 'umzug')) return 'move';
        if (str_contains($source, 'umzuege')) return 'move';
        if (str_contains($source, 'reinigung')) return 'cleaning';
        if (str_contains($source, 'maler')) return 'painting';
        if (str_contains($source, 'garten')) return 'gardening';
        if (str_contains($source, 'sanitaer')) return 'plumbing';

        return 'unknown';
    }

    protected function extractAddressData(array $fields): array
    {
        $candidates = [
            $fields['auszug_adresse'] ?? null,
            $fields['address'] ?? null,
            $fields['auszug_adresse_firma'] ?? null,
            $fields['einzug_adresse'] ?? null,
            $fields['einzug_adresse_firma'] ?? null,
        ];

        foreach ($candidates as $address) {
            if (is_array($address) && !empty($address['city']) && !empty($address['zip'])) {
                return [
                    'city' => $address['city'],
                    'zip' => $address['zip'],
                ];
            }
        }

        return [
            'city' => null,
            'zip' => null,
        ];
    }

    public function extractFieldsByType(string $type, array $formFields): array
    {
        return match ($type) {
            'move'      => $this->extractMoveFields($formFields),
            'cleaning'  => $this->extractCleaningFields($formFields),
            'painting'  => $this->extractPaintingFields($formFields),
            'gardening' => $this->extractGardeningFields($formFields),
            'plumbing'  => $this->extractPlumbingFields($formFields),
            default     => [],
        };
    }

    public function extractMoveFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;

        $fromCity = $getCity($formFields['auszug_adresse'] ?? $formFields['auszug_adresse_firma'] ?? null);
        $toCity = $getCity($formFields['einzug_adresse'] ?? $formFields['einzug_adresse_firma'] ?? null);

        return [
            'room_size'     => $formFields['auszug_flaeche'] ?? $formFields['auszug_flaeche_firma'] ?? null,
            'move_date'     => isset($formFields['datetime_1']) ? date('Y-m-d', strtotime(str_replace('/', '.', $formFields['datetime_1']))) : null,
            'from_city'     => $fromCity,
            'to_city'       => $toCity,
            'has_lift'      => $formFields['auszug_lift'] ?? $formFields['auszug_lift_firma'] ?? null,
            'customer_type' => isset($formFields['firmenname']) ? 'firma' : 'privat',
        ];
    }

    public function extractCleaningFields(array $formFields): array
    {
        return [
            'object_size'   => $formFields['wohnung_groesse'] ?? null,
            'cleaning_type' => $formFields['reinigungsart'] ?? null,
        ];
    }

    public function extractPaintingFields(array $formFields): array
    {
        return [
            'area'           => $formFields['wand_gesamtflaeche'] ?? null,
            'indoor_outdoor' => $formFields['malerart'] ?? null,
        ];
    }

    public function extractGardeningFields(array $formFields): array
    {
        return [
            'garden_size' => $formFields['bodenplatten_haus_flaeche'] ?? null,
            'work_type'   => $formFields['bodenplatten_vorplatz'] ?? null,
        ];
    }

    public function extractPlumbingFields(array $formFields): array
    {
        return [
            'problem_type' => $formFields['sanitaer_typ'] ?? null,
            'urgency'      => $formFields['dringlichkeit'] ?? null,
        ];
    }


    // z. B. in OfferModel:
    public function getOffersWithBookingPrice(int $userId = null)
    {
        $builder = $this->db->table('offers o')
            ->select('o.*, b.amount AS booking_price')
            ->join('bookings b', 'b.reference_id = o.id AND b.type = "offer_purchase"', 'left');

        if ($userId !== null) {
            $builder->where('b.user_id', $userId);
        }

        return $builder->get()->getResultArray();
    }

    public function getGroupedOffers(?int $userId = null): array
    {
        $builder = $this->db->table('offers')
            ->select('
            MIN(id) as id,
            group_id,
            GROUP_CONCAT(DISTINCT type SEPARATOR " + ") as type,
            GROUP_CONCAT(form_fields SEPARATOR "||SEP||") as form_fields_combined,
            MIN(title) as title,
            MIN(status) as status,
            MIN(price) as price,
            MIN(created_at) as created_at
        ')
            ->groupStart()
            ->whereNotIn('status', ['deleted'])
            ->groupEnd()
            ->groupBy('group_id');

        if ($userId !== null) {
            $builder->join('bookings', 'bookings.reference_id = offers.id AND bookings.type = "offer_purchase"', 'left');
            $builder->where('bookings.user_id', $userId);
        }

        return $builder->get()->getResultArray();
    }





}
