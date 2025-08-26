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

        // ---- Daten abrufen ----
        $since = $this->request->getGet('since');
        $query = $this->model
            ->select('offers.*, oc.*, oe.*, om.*')
            ->join('offers_move om', 'om.offer_id = offers.id', 'left')
            ->join('offers_cleaning oc', 'oc.offer_id = offers.id', 'left')
            ->join('offers_electrician oe', 'oe.offer_id = offers.id', 'left')
            ;

        if ($since) {
            $query = $query->where('updated_at >=', $since);
        }

        return $this->respond($query->findAll());
    }
}
