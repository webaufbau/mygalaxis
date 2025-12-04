<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class EmailLog extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Zeige alle E-Mail Logs mit Filtern
     */
    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'email_type' => $this->request->getGet('email_type'),
            'status' => $this->request->getGet('status'),
            'recipient_type' => $this->request->getGet('recipient_type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'limit' => $this->request->getGet('limit') ?? 100,
        ];

        $builder = $this->db->table('offer_email_log oel')
            ->select('oel.*, o.title as offer_title, o.uuid as offer_uuid, u.company_name')
            ->join('offers o', 'o.id = oel.offer_id', 'left')
            ->join('users u', 'u.id = oel.company_id', 'left')
            ->orderBy('oel.sent_at', 'DESC')
            ->limit((int)$filters['limit']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('oel.recipient_email', $search)
                ->orLike('oel.subject', $search)
                ->orLike('o.title', $search)
                ->orWhere('oel.offer_id', is_numeric($search) ? (int)$search : 0)
            ->groupEnd();
        }

        if (!empty($filters['email_type'])) {
            $builder->where('oel.email_type', $filters['email_type']);
        }

        if (!empty($filters['status'])) {
            $builder->where('oel.status', $filters['status']);
        }

        if (!empty($filters['recipient_type'])) {
            $builder->where('oel.recipient_type', $filters['recipient_type']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('oel.sent_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('oel.sent_at <=', $filters['date_to'] . ' 23:59:59');
        }

        $logs = $builder->get()->getResultArray();

        // Hole alle E-Mail-Typen für den Filter
        $emailTypes = $this->db->table('offer_email_log')
            ->distinct()
            ->select('email_type')
            ->orderBy('email_type')
            ->get()
            ->getResultArray();

        return view('admin/email_log/index', [
            'title' => 'E-Mail Verlauf',
            'logs' => $logs,
            'filters' => $filters,
            'emailTypes' => array_column($emailTypes, 'email_type'),
        ]);
    }

    /**
     * Zeige alle E-Mails für ein bestimmtes Angebot
     */
    public function byOffer(int $offerId)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($offerId);

        if (!$offer) {
            return redirect()->to('/admin/email-log')->with('error', 'Angebot nicht gefunden.');
        }

        $logs = $this->db->table('offer_email_log oel')
            ->select('oel.*, u.company_name')
            ->join('users u', 'u.id = oel.company_id', 'left')
            ->where('oel.offer_id', $offerId)
            ->orderBy('oel.sent_at', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/email_log/by_offer', [
            'title' => 'E-Mails für Angebot #' . $offerId,
            'logs' => $logs,
            'offer' => $offer,
        ]);
    }

    /**
     * Zeige alle E-Mails für eine bestimmte Firma
     */
    public function byCompany(int $companyId)
    {
        $userModel = new \App\Models\UserModel();
        $company = $userModel->find($companyId);

        if (!$company) {
            return redirect()->to('/admin/email-log')->with('error', 'Firma nicht gefunden.');
        }

        $logs = $this->db->table('offer_email_log oel')
            ->select('oel.*, o.title as offer_title')
            ->join('offers o', 'o.id = oel.offer_id', 'left')
            ->where('oel.company_id', $companyId)
            ->orderBy('oel.sent_at', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        return view('admin/email_log/by_company', [
            'title' => 'E-Mails für ' . ($company->company_name ?? 'Firma #' . $companyId),
            'logs' => $logs,
            'company' => $company,
        ]);
    }
}
