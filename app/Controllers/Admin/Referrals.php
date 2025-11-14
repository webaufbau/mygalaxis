<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReferralModel;
use App\Models\UserModel;
use App\Models\BookingModel;

class Referrals extends BaseController
{
    protected $referralModel;
    protected $userModel;
    protected $bookingModel;

    public function __construct()
    {
        $this->referralModel = new ReferralModel();
        $this->userModel = new UserModel();
        $this->bookingModel = new BookingModel();
    }

    /**
     * Admin Referral-Übersicht
     */
    public function index()
    {
        // Filter-Parameter auslesen
        $status = $this->request->getGet('status') ?? '';
        $referrerId = $this->request->getGet('referrer_id') ?? '';
        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo = $this->request->getGet('date_to') ?? '';
        $search = $this->request->getGet('search') ?? '';

        // Filter aufbauen
        $filters = [];
        if (!empty($status)) $filters['status'] = $status;
        if (!empty($referrerId)) $filters['referrer_id'] = $referrerId;
        if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
        if (!empty($dateTo)) $filters['date_to'] = $dateTo;
        if (!empty($search)) $filters['search'] = $search;

        // Hole alle Referrals
        $referrals = $this->referralModel->getAllReferrals($filters);

        // Hole alle manuellen Gutschriften (nur vom Admin erstellte, nicht Kunden-Aufladungen)
        $db = \Config\Database::connect();
        $manualCredits = $db->table('bookings')
            ->select('bookings.*, users.company_name, users.username as user_email')
            ->join('users', 'users.id = bookings.user_id')
            ->where('bookings.payment_method', 'manual_credit')
            ->where('bookings.type', 'topup')
            ->notLike('bookings.description', 'Guthabenaufladung%')
            ->notLike('bookings.description', 'Guthaben aufgeladen%')
            ->orderBy('bookings.created_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        // IP-Warnung: Prüfe ob Vermittler und Vermittelter gleiche IP haben (Fake-Verdacht)
        foreach ($referrals as &$referral) {
            $referral['ip_warning'] = false;

            if (!empty($referral['ip_address']) && !empty($referral['referrer_user_id'])) {
                // Hole letzte IP des Vermittlers
                $db = \Config\Database::connect();
                $referrerIp = $db->table('referrals')
                    ->select('ip_address')
                    ->where('referred_user_id', $referral['referrer_user_id'])
                    ->orderBy('created_at', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRow();

                // Wenn keine Referrer-IP gefunden, versuche aus anderen Quellen
                if (!$referrerIp) {
                    // Könnte man noch erweitern mit Login-IPs etc.
                }

                // Warnung wenn gleiche IP
                if ($referrerIp && $referrerIp->ip_address === $referral['ip_address']) {
                    $referral['ip_warning'] = true;
                }
            }
        }

        // Alle Firmen für Dropdown
        $companies = $this->userModel
            ->select('id, company_name, username')
            ->where('company_name !=', '')
            ->orderBy('company_name', 'ASC')
            ->asArray()
            ->findAll();

        return view('admin/referrals/index', [
            'referrals' => $referrals,
            'companies' => $companies,
            'manualCredits' => $manualCredits,
            'filters' => [
                'status' => $status,
                'referrer_id' => $referrerId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
            ]
        ]);
    }

    /**
     * Gutschrift für ein Referral geben
     */
    public function giveCredit($id)
    {
        $user = auth()->user();

        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/admin/referrals')->with('error', 'Keine Berechtigung.');
        }

        $referral = $this->referralModel->find($id);

        if (!$referral) {
            return redirect()->to('/admin/referrals')->with('error', 'Referral nicht gefunden.');
        }

        if ($referral['status'] === 'credited') {
            return redirect()->to('/admin/referrals')->with('error', 'Gutschrift wurde bereits gegeben.');
        }

        // Betrag aus POST oder Standard 50 CHF
        $amount = $this->request->getPost('amount') ?? 50.00;
        $note = $this->request->getPost('note') ?? 'Weiterempfehlungs-Gutschrift genehmigt';

        $success = $this->referralModel->giveCredit($id, $user->id, $amount, $note);

        if ($success) {
            return redirect()->to('/admin/referrals')->with('success', 'Gutschrift wurde erfolgreich gegeben.');
        } else {
            return redirect()->to('/admin/referrals')->with('error', 'Fehler beim Geben der Gutschrift.');
        }
    }

    /**
     * Referral ablehnen
     */
    public function reject($id)
    {
        $user = auth()->user();

        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/admin/referrals')->with('error', 'Keine Berechtigung.');
        }

        $note = $this->request->getPost('note') ?? 'Abgelehnt durch Admin';

        $success = $this->referralModel->rejectReferral($id, $note);

        if ($success) {
            return redirect()->to('/admin/referrals')->with('success', 'Referral wurde abgelehnt.');
        } else {
            return redirect()->to('/admin/referrals')->with('error', 'Fehler beim Ablehnen.');
        }
    }

    /**
     * Manuelle Gutschrift für eine Firma (unabhängig von Referrals)
     */
    public function manualCredit()
    {
        $user = auth()->user();

        if (!$user || !$user->inGroup('admin')) {
            return redirect()->back()->with('error', 'Keine Berechtigung.');
        }

        // GET: Formular anzeigen
        if (strtolower($this->request->getMethod()) === 'get') {
            $companies = $this->userModel
                ->select('id, company_name, username')
                ->where('company_name !=', '')
                ->orderBy('company_name', 'ASC')
                ->asArray()
                ->findAll();

            return view('admin/referrals/manual_credit', [
                'companies' => $companies
            ]);
        }

        // POST: Gutschrift verarbeiten
        $companyId = $this->request->getPost('company_id');
        $amount = $this->request->getPost('amount');
        $reason = $this->request->getPost('reason');

        if (!$companyId || !$amount || $amount <= 0) {
            return redirect()->back()->with('error', 'Bitte alle Felder ausfüllen.')->withInput();
        }

        $company = $this->userModel->find($companyId);

        if (!$company) {
            return redirect()->back()->with('error', 'Firma nicht gefunden.')->withInput();
        }

        // Booking erstellen
        $bookingData = [
            'user_id' => $companyId,
            'type' => 'topup',
            'amount' => $amount,
            'paid_amount' => 0,
            'payment_method' => 'manual_credit',
            'description' => $reason ?: 'Manuelle Gutschrift durch Admin',
            'status' => 'completed',
        ];

        $bookingId = $this->bookingModel->insert($bookingData);

        if ($bookingId) {
            log_message('info', sprintf(
                'Manual credit: %s CHF given to user #%d (%s) by admin #%d. Reason: %s',
                $amount,
                $companyId,
                $company->company_name,
                $user->id,
                $reason
            ));

            return redirect()->to('/admin/referrals/manual-credit')->with('success', 'Gutschrift wurde erfolgreich gegeben.');
        } else {
            return redirect()->back()->with('error', 'Fehler beim Erstellen der Gutschrift.')->withInput();
        }
    }
}
