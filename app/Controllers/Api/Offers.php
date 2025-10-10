<?php
namespace App\Controllers\Api;

use App\Models\OfferModel;
use CodeIgniter\RESTful\ResourceController;

class Offers extends ResourceController
{
    protected $modelName = OfferModel::class;
    protected $format    = 'json';

    public function index()
    {
        $api_config = Config('SyncApi');

        log_message('info', 'API request received', [
            'endpoint' => $this->request->getUri()->getPath(),
            'method'   => $this->request->getMethod(),
            'params'   => $this->request->getGetPost(),
            'ip'       => $this->request->getIPAddress(),
            'headers'  => [
                'X-API-Timestamp' => $this->request->getHeaderLine('X-API-Timestamp'),
                'X-API-Signature' => $this->request->getHeaderLine('X-API-Signature'),
            ],
        ]);

        // ---- Signaturprüfung ----
        $ts  = $this->request->getHeaderLine('X-API-Timestamp');
        $sig = $this->request->getHeaderLine('X-API-Signature');

        $secret = $api_config->apiKey;
        $payload = $ts . ':GET:/api/offers';
        $expectedSig = hash_hmac('sha256', $payload, $secret);

        // Timestamp prüfen (±300 Sekunden erlaubt)
        if (!$ts || abs(time() - (int)$ts) > 300) {
            return $this->fail('Timestamp expired', 401);
        }

        // Signatur vergleichen
        if (!$sig || !hash_equals($expectedSig, $sig)) {
            return $this->fail('Invalid signature', 401);
        }

        $subTables = [
            'offers_cleaning',
            'offers_electrician',
            'offers_flooring',
            'offers_gardening',
            'offers_heating',
            'offers_move',
            'offers_move_cleaning',
            'offers_painting',
            'offers_plumbing',
            'offers_tiling'
        ];

        // ---- Query bauen ----
        $model = $this->model->orderBy('offers.id', 'ASC')->select('offers.*');
        $db    = $this->model->db; // DB-Connection

        foreach ($subTables as $table) {
            // Join
            $model->join($table, "$table.offer_id = offers.id", 'left');

            // Feldnamen holen (CI4 kann getFieldNames oder getFieldData haben)
            $fields = method_exists($db, 'getFieldNames')
                ? $db->getFieldNames($table)
                : array_map(static fn($f) => $f->name, $db->getFieldData($table));

            // Aliassierte Selects hinzufügen: table.col AS table__col
            foreach ($fields as $field) {
                $model->select("$table.$field AS {$table}__{$field}");
            }
        }

        // Optionaler Filter: Neue UND aktualisierte Einträge seit $since
        $since = $this->request->getGet('since');
        if ($since) {
            $model->groupStart()
                  ->where('offers.created_at >=', $since)      // Neue Einträge
                  ->orWhere('offers.updated_at >=', $since)    // Aktualisierte Einträge
                  ->groupEnd();
        }

        // ---- EINMAL ausführen ----
        $rows = $model->findAll();

        // ---- Flache Spalten -> geschachtelte Extras mappen ----
        $out = [];
        foreach ($rows as $row) {
            $extras = [];

            foreach ($subTables as $table) {
                $prefix = $table . '__';
                $sub    = [];

                // Felder dieser Subtable in ein Unterobjekt verschieben
                foreach ($row as $k => $v) {
                    if (strpos($k, $prefix) === 0) {
                        $subField = substr($k, strlen($prefix));
                        $sub[$subField] = $v;
                        unset($row[$k]); // oben entfernen, unten schachteln
                    }
                }

                // nur behalten, wenn da auch wirklich was drin ist (nicht alles null/leer)
                $hasData = false;
                foreach ($sub as $v) {
                    if ($v !== null && $v !== '') { $hasData = true; break; }
                }
                if ($hasData) {
                    $extras[$table] = $sub;
                }
            }

            // bevorzugt die Subtable, die zum offers.type passt (z.B. type=painting -> offers_painting)
            $row['extra'] = null;
            if (!empty($extras)) {

                // Wenn du ALLE behalten willst, nimm das rein; sonst zeile auskommentieren
                $row['extras'] = $extras;
            }

            $out[] = $row;
        }

        // LastQuery NACH der Ausführung loggen
        log_message('info', 'SQL: ' . (string) $this->model->db->getLastQuery());

        // Fertig ausliefern
        return $this->respond($out);
    }
}
