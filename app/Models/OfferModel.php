<?php
namespace App\Models;

use CodeIgniter\Model;
use DateTime;
use Random\RandomException;

class OfferModel extends Model
{
    /**
     * Maximale Anzahl Käufe pro Offerte bevor sie ausverkauft ist
     */
    public const MAX_PURCHASES = 4;

    protected $table = 'offers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'type',
        'original_type',
        'sub_type',
        'title',
        'status',
        'price',
        'discounted_price',
        'buyers',
        'bought_by',
        'language',
        'firstname',
        'lastname',
        'company',
        'email',
        'phone',
        'work_start_date',
        'additional_service',
        'service_url',
        'uuid',
        'customer_type',
        'city',
        'zip',
        'country',
        'platform',
        'form_fields',
        'form_fields_combo',
        'headers',
        'referer',
        'verified',
        'verify_type',
        'from_campaign',
        'checked_at',
        'reminder_sent_at',
        'verification_token',
        'form_name',
        'group_id',
        'request_session_id',
        'access_hash',
        'is_test',
        'custom_price',
        'admin_notes',
        'customer_hint',
        'edited_at',
        'edited_by',
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

        // Generiere access_hash wenn nicht vorhanden
        if (empty($data['data']['access_hash'])) {
            $data['data']['access_hash'] = md5(uniqid() . time() . rand(1000, 9999));
            log_message('debug', 'Auto-generierter access_hash: ' . $data['data']['access_hash']);
        }

        return $data;
    }

    /**
     * Stellt sicher dass die Offer einen access_hash hat
     * Generiert einen neuen falls nicht vorhanden
     */
    public function ensureAccessHash($offerId): string
    {
        $offer = $this->find($offerId);

        if (!$offer) {
            throw new \RuntimeException("Offer mit ID {$offerId} nicht gefunden");
        }

        // Wenn access_hash bereits existiert, zurückgeben
        if (!empty($offer['access_hash'])) {
            return $offer['access_hash'];
        }

        // Generiere neuen access_hash
        $accessHash = md5($offerId . uniqid() . time() . rand(1000, 9999));

        // Speichere in DB
        $db = \Config\Database::connect();
        $db->table('offers')
            ->where('id', $offerId)
            ->update(['access_hash' => $accessHash]);

        log_message('info', "access_hash generiert für Offer #{$offerId}: {$accessHash}");

        return $accessHash;
    }

    /**
     * @throws RandomException
     */
    public function enrichDataFromFormFields(array $formFields, array $original = []): array
    {
        $userInputs = $formFields['__submission']['user_inputs'] ?? [];
        $originalType = $original['type'] ?? null;

        // Adresse extrahieren
        $address = $this->extractAddressData($formFields);

        $data = [];

        if (!$original || !isset($original['type'])) {
            $data['type'] = $this->detectType($formFields); // Grobe Kategorie
        }

        // Exakter Typ (aus Formularfeld)
        $exactType = $formFields['type']
            ?? $formFields['service_type']
            ?? $formFields['angebot_typ']
            ?? $originalType
            ?? $original['type']
            ?? $formFields['_wp_http_referer']
            ?? $formFields['__submission']['source_url']
            ?? $formFields['service_url']
            ?? ''
            ?? null;

        // Exakten Typ immer mitschreiben, falls vorhanden
        if ($exactType) {
            $data['original_type'] = strtolower(trim($exactType));
            // Aktualisiere $originalType für sub_type Extraktion
            $originalType = $data['original_type'];
        }

        // Sub-Type aus original_type extrahieren (nur wenn nicht move_cleaning)
        if (!empty($originalType) && (($original['type'] ?? null) !== 'move_cleaning')) {
            $parts = explode('_', $originalType, 2); // in maximal 2 Teile aufteilen
            $data['sub_type'] = $parts[1] ?? $parts[0]; // falls kein Unterstrich, nimm das Original
        }


        $data['city'] = $address['city'];
        $data['zip'] = $address['zip'];
        $data['customer_type'] = isset($formFields['firmenname']) ? 'firma' : 'privat';

        if(isset($formFields['language'])) {
            $data['language'] = $formFields['language'];
        } elseif(isset($formFields['lang'])) {
            $data['language'] = $formFields['lang'];
        } else {
            $data['language'] = 'de';
        }

        // Platform normalisieren: Domain-Format zu Ordner-Format
        // z.B. offertenheld.ch -> my_offertenheld_ch
        // z.B. renovoscout24.de -> my_renovoscout24_de
        $platformRaw = $formFields['platform'] ?? null;
        if ($platformRaw) {
            // Wenn schon im Ordner-Format (beginnt mit my_), direkt übernehmen
            if (strpos($platformRaw, 'my_') === 0) {
                $data['platform'] = $platformRaw;
            } else {
                // Domain-Format: Punkte durch Underscores ersetzen und my_ voranstellen
                $data['platform'] = 'my_' . str_replace('.', '_', $platformRaw);
            }
        } else {
            $data['platform'] = null;
        }

        $data['firstname'] = $formFields['vorname'] ?? $userInputs['vorname'] ?? null;
        $data['lastname'] = $formFields['nachname'] ?? $userInputs['nachname'] ?? null;
        $data['company'] = $formFields['firma'] ?? $formFields['firmenname'] ?? $formFields['company'] ?? $userInputs['firma'] ?? $userInputs['firmenname'] ?? null;
        $data['email'] = $formFields['email'] ?? $userInputs['email'] ?? $formFields['email_firma'] ?? $userInputs['email_firma'] ?? null;
        $data['phone'] = $formFields['phone'] ?? $userInputs['phone'] ?? null;
        $data['additional_service'] = $formFields['additional_service'] ?? null;
        $data['service_url'] = $formFields['service_url'] ?? null;

        // Startdatum formatieren
        if (!empty($formFields['datetime_1'])) {
            $date = DateTime::createFromFormat('d/m/Y', $formFields['datetime_1']);
            $timestamp = $date ? $date->getTimestamp() : false;
            $data['work_start_date'] = $timestamp ? date("Y-m-d", $timestamp) : null;
        } else {
            $data['work_start_date'] = null;
        }

        // UUID nur setzen, wenn noch nicht vorhanden
        if (empty($original['uuid'])) {
            $data['uuid'] = bin2hex(random_bytes(16));
        }

        return $data;
    }


    protected function detectType(array $fields): string
    {
        // Feldname, in dem der Wert erwartet wird
        $typeValue = $fields['type']
            ?? $fields['service_type']
            ?? $fields['angebot_typ']
            ?? $fields['_wp_http_referer']
            ?? $fields['__submission']['source_url']
            ?? $fields['service_url']
            ?? ''
            ?? null;

        if (!$typeValue) {
            return 'unknown';
        }

        // Lade Subtype Mapping aus zentraler Config
        $subtypeConfig = config('OfferSubtypes');
        $mapping = $subtypeConfig->subtypeToTypeMapping;

        // Kleinbuchstaben und Whitespace entfernen, um Fehler zu vermeiden
        $normalized = strtolower(trim($typeValue));

        return $mapping[$normalized] ?? 'unknown';
    }

    /**
     * Extrahiere den Subtype aus den Formularfeldern
     * Der Subtype ist der ursprüngliche Wert (z.B. 'umzug_privat', 'reinigung_wohnung')
     *
     * @param array $fields
     * @return string|null
     */
    public function detectSubtype(array $fields): ?string
    {
        $typeValue = $fields['type']
            ?? $fields['service_type']
            ?? $fields['angebot_typ']
            ?? $fields['_wp_http_referer']
            ?? $fields['__submission']['source_url']
            ?? $fields['service_url']
            ?? null;

        if (!$typeValue) {
            return null;
        }

        $normalized = strtolower(trim($typeValue));

        // Prüfe ob dieser Wert ein bekannter Subtype ist
        $subtypeConfig = config('OfferSubtypes');
        if (isset($subtypeConfig->subtypeToTypeMapping[$normalized])) {
            return $normalized;
        }

        return null;
    }


    protected function extractAddressData(array $fields): array
    {
        // WICHTIG: Zuerst prüfen ob Felder direkt in $fields vorhanden sind (z.B. von skip_kontakt)
        if (!empty($fields['city']) && !empty($fields['zip'])) {
            return [
                'city' => $fields['city'],
                'zip' => $fields['zip'],
            ];
        }

        // Ansonsten in verschachtelten Arrays suchen
        $candidates = [
            $fields['address'] ?? null,
            $fields['auszug_adresse'] ?? null,
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
            'move'       => $this->extractMoveFields($formFields),
            'cleaning'   => $this->extractCleaningFields($formFields),
            'painting'   => $this->extractPaintingFields($formFields),
            'gardening'  => $this->extractGardeningFields($formFields),
            'plumbing'   => $this->extractPlumbingFields($formFields),
            'electrician'=> $this->extractElectricianFields($formFields),
            'flooring'   => $this->extractFlooringFields($formFields),
            'heating'    => $this->extractHeatingFields($formFields),
            'tiling'     => $this->extractTilingFields($formFields),
            default      => [],
        };
    }

    public function extractMoveFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;

        $fromCity = $getCity($formFields['auszug_adresse'] ?? $formFields['auszug_adresse_firma'] ?? null);
        $toCity = $getCity($formFields['einzug_adresse'] ?? $formFields['einzug_adresse_firma'] ?? null);

        return [
            'from_object_type' => $formFields['auszug_object'] ?? $formFields['auszug_object_firma'] ?? null,
            'from_city'     => $fromCity,
            'from_room_count' => $formFields['auszug_zimmer'] ?? null,
            'to_object_type' => $formFields['einzug_object'] ?? $formFields['einzug_object_firma'] ?? null,
            'to_city'       => $toCity,
            'to_room_count' => $formFields['einzug_zimmer'] ?? null,
            'service_details' => $formFields['details_leistungen'] ?? null,
            'move_date'     => isset($formFields['datetime_1']) ? date('Y-m-d', strtotime(str_replace('/', '.', $formFields['datetime_1']))) : null,
            'customer_type' => isset($formFields['auszug_object_firma']) ? 'company' : 'private',
        ];
    }

    public function extractCleaningFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;
        $address_city = $getCity($formFields['address'] ?? null);

        return [
            'user_role'        => $formFields['benutzer'] ?? null, // Mieter, Eigentümer
            'business_type' => $formFields['gewerbeart'] ?? null, // Büro, Laden, Praxis, Andere
            'object_type' => $formFields['objektart'] ?? null, // Einfamilienhaus, Mehrfamilienhaus, Gewerbe
            'client_role' => $formFields['auftraggeber'] ?? null, // Eigentümer, Verwaltung, Andere
            'apartment_size'   => $formFields['wohnung_groesse'] ?? null,
            'room_count'       => $formFields['komplett_anzahlzimmer'] ?? null,
            'cleaning_area_sqm'=> $formFields['reinigungsflaeche_qm'] ?? null,
            'cleaning_type'=> $formFields['reinigungsart'] ?? null,
            'window_shutter_cleaning' => $formFields['reinigung_fenster_rollaeden'] ?? null,
            'facade_count' => $formFields['aussenfassade_anzahl'] ?? null,
            'address_city'=> $address_city,
        ];
    }

    public function extractPaintingFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;
        $address_city = $getCity($formFields['address'] ?? null);

        return [
            'object_type'           => $formFields['art_objekt'] ?? $formFields['art_objekt_1'] ?? null,
            'business_type' => $formFields['art_gewerbe'] ?? null,
            'painting_overview' => $formFields['malerarbeiten_uebersicht'] ?? $formFields['malerarbeiten_uebersicht_1'] ?? null, // Um welche Malerarbeiten handelt es sich? Innenräume Fassade Andere
            'service_details' => $formFields['arbeiten_wohnung'] ?? $formFields['arbeiten_wohnung_1'] ?? null,
            'address_city'=> $address_city,
        ];
    }

    public function extractGardeningFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;
        $address_city = $getCity($formFields['address'] ?? null);

        return [
            'user_role' => $formFields['garten_benutzer'] ?? null, // Mieter, Eigentümer, Verwaltung, Andere
            'service_details'   => $formFields['garten_anlegen'] ?? null, // Bodenplatten verlegen, Kies/Split Flächen, ...
            'address_city'=> $address_city,
        ];
    }

    public function extractElectricianFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;
        $address_city = $getCity($formFields['address'] ?? null);

        return [
            'object_type'           => $formFields['art_objekt'] ?? null,
            'service_details'      => $formFields['arbeiten_elektriker'] ?? null,
            'address_city'=> $address_city,
        ];
    }

    public function extractPlumbingFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;
        $address_city = $getCity($formFields['address'] ?? null);

        return [
            'object_type'           => $formFields['art_objekt'] ?? null,
            'service_details'      => $formFields['arbeiten_sanitaer'] ?? null,
            'address_city'=> $address_city,
        ];
    }

    public function extractHeatingFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;
        $address_city = $getCity($formFields['address'] ?? null);

        return [
            'object_type'           => $formFields['art_objekt'] ?? null,
            'service_details'      => $formFields['arbeiten_heizung'] ?? null,
            'address_city'=> $address_city,
        ];
    }

    public function extractTilingFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;
        $address_city = $getCity($formFields['address'] ?? null);

        return [
            'object_type'           => $formFields['art_objekt'] ?? null,
            'service_details'      => $formFields['arbeiten_platten'] ?? null,
            'address_city'=> $address_city,
        ];
    }

    public function extractFlooringFields(array $formFields): array
    {
        $getCity = fn($block) => is_array($block) ? ($block['city'] ?? null) : null;
        $address_city = $getCity($formFields['address'] ?? null);

        return [
            'object_type'           => $formFields['art_objekt'] ?? null,
            'service_details'      => $formFields['arbeiten_boden'] ?? null,
            'address_city'=> $address_city,
        ];
    }


    // z.B. in OfferModel:
    public function getOffersWithBookingPrice(?int $userId = null)
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
