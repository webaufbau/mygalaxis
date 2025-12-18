<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MonthlyInvoiceModel;
use App\Models\UserModel;

class Invoices extends BaseController
{
    protected $monthlyInvoiceModel;
    protected $userModel;

    public function __construct()
    {
        $this->monthlyInvoiceModel = new MonthlyInvoiceModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Filter-Parameter auslesen
        $periodFrom = $this->request->getGet('period_from') ?? '';
        $periodTo = $this->request->getGet('period_to') ?? '';
        $platform = $this->request->getGet('platform') ?? '';
        $categories = $this->request->getGet('categories') ?? [];
        $companyName = $this->request->getGet('company_name') ?? '';

        // Hole alle Firmen mit ihrer ersten Transaktion (egal ob topup oder purchase)
        $db = \Config\Database::connect();

        $companiesBuilder = $db->table('users')
            ->select('users.id as user_id, users.username as email, users.company_name, users.platform, MIN(bookings.created_at) as first_transaction')
            ->join('bookings', 'bookings.user_id = users.id', 'inner');

        // Filter auf Firmen anwenden
        if (!empty($platform)) {
            $companiesBuilder->where('users.platform', $platform);
        }

        if (!empty($companyName)) {
            $companiesBuilder->groupStart();
            $companiesBuilder->like('users.company_name', $companyName);
            $companiesBuilder->orLike('users.username', $companyName);
            $companiesBuilder->groupEnd();
        }

        // Branchen-Filter - nur Firmen die diese Branchen haben
        if (!empty($categories)) {
            $companiesBuilder->where('users.filter_categories IS NOT NULL');
            $companiesBuilder->where('users.filter_categories !=', '');

            $companiesBuilder->groupStart();
            foreach ($categories as $category) {
                $companiesBuilder->orWhere("FIND_IN_SET('{$db->escapeString($category)}', users.filter_categories) >", 0);
            }
            $companiesBuilder->groupEnd();
        }

        // GroupBy am Ende
        $companiesBuilder->groupBy('users.id');

        // Debug: Last Query nach get()
        $companies = $companiesBuilder->get()->getResultArray();
        $lastQuery = $db->getLastQuery();
        log_message('debug', 'Invoice Filter FULL SQL: ' . $lastQuery);

        // Debug: Log filter info
        log_message('debug', 'Invoice Filters - Platform: ' . ($platform ?: 'none') .
                    ', Categories: ' . json_encode($categories) .
                    ', Company Name: ' . ($companyName ?: 'none'));
        log_message('debug', 'Filtered companies count: ' . count($companies));

        // Für jede Firma: Generiere Rechnungen für JEDEN Monat seit erstem Kauf
        $invoices = [];
        $currentMonth = date('Y-m');

        foreach ($companies as $company) {
            if (empty($company['first_transaction'])) {
                continue; // Überspringe Firmen ohne Transaktionen
            }

            $firstTransactionDate = new \DateTime($company['first_transaction']);
            $startPeriod = $firstTransactionDate->format('Y-m');

            // Generiere Rechnungen für JEDEN Monat seit erster Transaktion
            // Rechnung vom 1. Dezember zeigt November-Käufe, etc.
            $period = $startPeriod;
            $lastMonth = date('Y-m', strtotime('-1 month')); // Vormonat (aktueller Monat noch nicht abgeschlossen)

            while ($period <= $lastMonth) {
                // Filter: Periode von/bis
                if (!empty($periodFrom) && $period < $periodFrom) {
                    $period = date('Y-m', strtotime($period . '-01 +1 month'));
                    continue;
                }
                if (!empty($periodTo) && $period > $periodTo) {
                    break;
                }

                // Hole Käufe und Stornierungen für diesen Monat
                $startDate = $period . '-01 00:00:00';
                $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

                $purchases = $db->table('bookings')
                    ->select('id, created_at, paid_amount, amount, type')
                    ->where('user_id', $company['user_id'])
                    ->whereIn('type', ['offer_purchase', 'refund_purchase'])
                    ->where('created_at >=', $startDate)
                    ->where('created_at <=', $endDate)
                    ->get()
                    ->getResultArray();

                $totalAmount = 0;
                $purchaseCount = 0;
                $refundCount = 0;
                foreach ($purchases as $purchase) {
                    if ($purchase['type'] === 'offer_purchase') {
                        $totalAmount += abs($purchase['paid_amount'] ?? $purchase['amount']);
                        $purchaseCount++;
                    } else {
                        // Stornierung: Betrag abziehen
                        $totalAmount -= abs($purchase['amount']);
                        $refundCount++;
                    }
                }

                // Rechnungsnummer generieren
                $year = substr($period, 0, 4);
                $month = substr($period, 5, 2);

                // Extrahiere Land aus Plattform (z.B. my_offertenschweiz_ch -> CH)
                $platformParts = explode('_', $company['platform'] ?? '');
                $countryCode = end($platformParts);
                $country = strtoupper($countryCode === 'ch' ? 'CH' :
                          ($countryCode === 'de' ? 'DE' :
                          ($countryCode === 'at' ? 'AT' : 'CH')));

                // Rechnung wird am 1. des NÄCHSTEN Monats ausgestellt
                $invoiceDate = date('Y-m-01 00:00:00', strtotime($period . '-01 +1 month'));

                // Währung basierend auf Land
                $currency = ($country === 'CH') ? 'CHF' : 'EUR';

                $invoices[] = [
                    'user_id' => $company['user_id'],
                    'company_name' => $company['company_name'],
                    'email' => $company['email'],
                    'platform' => $company['platform'],
                    'period' => $period, // Zeigt Vormonat (z.B. "2025-10" für Oktober-Käufe)
                    'purchase_count' => $purchaseCount,
                    'refund_count' => $refundCount,
                    'amount' => $totalAmount,
                    'currency' => $currency,
                    'created_at' => $invoiceDate, // Ausgestellt am 1. des Folgemonats
                    'invoice_number' => "M{$country}-{$year}{$month}-{$company['user_id']}",
                ];

                // Nächster Monat
                $period = date('Y-m', strtotime($period . '-01 +1 month'));
            }
        }

        // Sortiere Rechnungen: Zuerst nach Periode (neueste zuerst), dann nach Firmenname
        usort($invoices, function($a, $b) {
            $periodCompare = strcmp($b['period'], $a['period']);
            if ($periodCompare !== 0) return $periodCompare;

            // Wenn company_name leer, verwende email
            $nameA = !empty($a['company_name']) ? $a['company_name'] : $a['email'];
            $nameB = !empty($b['company_name']) ? $b['company_name'] : $b['email'];
            return strcmp($nameA, $nameB);
        });

        // Plattformen für Dropdown - nur für aktuelles Land
        $siteConfig = siteconfig();
        $siteCountry = strtoupper($siteConfig->siteCountry ?? 'CH');

        $allPlatforms = [
            'CH' => [
                'my_offertenschweiz_ch' => 'Offertenschweiz.ch',
                'my_offertenheld_ch' => 'Offertenheld.ch',
                'my_renovo24_ch' => 'Renovo24.ch',
            ],
            'DE' => [
                'my_offertendeutschland_de' => 'Offertendeutschland.de',
                'my_renovoscout24_de' => 'Renovoscout24.de',
                'my_offertenheld_de' => 'Offertenheld.de',
            ],
            'AT' => [
                'my_offertenaustria_at' => 'Offertenaustria.at',
                'my_offertenheld_at' => 'Offertenheld.at',
                'my_renovo24_at' => 'Renovo24.at',
            ],
        ];

        $platforms = $allPlatforms[$siteCountry] ?? $allPlatforms['CH'];

        // Kategorien für Multi-Select (aus Settings-Dateien)
        $categoryManager = new \App\Libraries\CategoryManager();
        $categoriesData = $categoryManager->getAll();
        $allCategories = $categoriesData['categories'] ?? [];

        return view('admin/invoices/index', [
            'invoices' => $invoices,
            'platforms' => $platforms,
            'allCategories' => $allCategories,
            'filters' => [
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
                'platform' => $platform,
                'categories' => $categories,
                'company_name' => $companyName,
            ]
        ]);
    }

    /**
     * PDF-Download für eine Monatsrechnung (Admin-Sicht)
     */
    public function downloadPdf($period, $userId)
    {
        // Hole User-Daten
        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'Benutzer nicht gefunden.');
        }

        // Hole alle Käufe für diesen Monat
        $bookingModel = new \App\Models\BookingModel();
        $year = substr($period, 0, 4);
        $month = substr($period, 5, 2);

        $startDate = $period . '-01 00:00:00';
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $bookings = $bookingModel
            ->where('user_id', $userId)
            ->whereIn('type', ['offer_purchase', 'refund_purchase'])
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Berechne Gesamtbetrag (Käufe abzüglich Stornierungen)
        $totalAmount = 0;
        $purchaseCount = 0;
        $refundCount = 0;
        foreach ($bookings as $booking) {
            if ($booking['type'] === 'offer_purchase') {
                $totalAmount += abs($booking['paid_amount'] ?? $booking['amount']);
                $purchaseCount++;
            } else {
                // Stornierung: Betrag abziehen
                $totalAmount -= abs($booking['amount']);
                $refundCount++;
            }
        }

        // Land aus User-Platform extrahieren (z.B. my_offertenschweiz_ch -> CH)
        $platformParts = explode('_', $user->platform ?? '');
        $countryCode = end($platformParts);
        $country = strtoupper($countryCode === 'ch' ? 'CH' :
                   ($countryCode === 'de' ? 'DE' :
                   ($countryCode === 'at' ? 'AT' : 'CH')));

        // Generiere Rechnungsnummer
        $invoiceNumber = "M{$country}-{$year}{$month}-{$userId}";

        // Erstelle Invoice-Array für Template
        $invoice = [
            'invoice_number' => $invoiceNumber,
            'period' => $period,
            'amount' => $totalAmount,
            'purchase_count' => $purchaseCount,
            'refund_count' => $refundCount,
            'created_at' => date('Y-m-01', strtotime($period . '-01 +1 month')),
        ];

        $html = view('account/pdf_monthly_invoice', [
            'user' => $user,
            'bookings' => $bookings,
            'invoice' => $invoice,
            'invoice_name' => $invoiceNumber,
            'country' => $country,
            'year' => $year,
            'month' => $month,
            'total' => $totalAmount
        ]);

        $mpdf = new \Mpdf\Mpdf(['default_font' => 'helvetica']);
        $mpdf->WriteHTML($html);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output($invoiceNumber . ".pdf", 'S'));
    }
}
