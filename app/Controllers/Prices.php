<?php namespace App\Controllers;

use CodeIgniter\Controller;

class Prices extends Controller
{
    public function index()
    {
        // Beispiel-Daten für die Preisliste
        $prices = [
            'Bis 1 Zimmer' => [
                'Umzug' => ['Annehmen' => 15, 'Reduzierter Preis' => 12],
                'Reinigung' => ['Annehmen' => 8, 'Reduzierter Preis' => 6],
                'Umzug inkl. Reinigung' => ['Annehmen' => 19, 'Reduzierter Preis' => 15],
            ],
            'Bis 1.5 Zimmer' => [
                'Umzug' => ['Annehmen' => 18, 'Reduzierter Preis' => 14],
                'Reinigung' => ['Annehmen' => 10, 'Reduzierter Preis' => 8],
                'Umzug inkl. Reinigung' => ['Annehmen' => 22, 'Reduzierter Preis' => 18],
            ],
            'Bis 2.5 Zimmer' => [
                'Umzug' => ['Annehmen' => 22, 'Reduzierter Preis' => 18],
                'Reinigung' => ['Annehmen' => 14, 'Reduzierter Preis' => 11],
                'Umzug inkl. Reinigung' => ['Annehmen' => 27, 'Reduzierter Preis' => 22],
            ],
            'Bis 5 Zimmer' => [
                'Umzug' => ['Annehmen' => 30, 'Reduzierter Preis' => 25],
                'Reinigung' => ['Annehmen' => 20, 'Reduzierter Preis' => 16],
                'Umzug inkl. Reinigung' => ['Annehmen' => 40, 'Reduzierter Preis' => 35],
            ],
        ];

        $data = [
            'title' => 'Preisliste der Anfragen für Offerten',
            'prices' => $prices,
            'info' => 'Übermittlungsgebühren für Anfragen: Übermittelte Anfragen an registrierte Unternehmen sind gebührenpflichtig. Anfragen können mit hinterlegten Zahlungsmitteln bezahlt werden. Gebühren sind pro Anfrage.',
        ];

        return view('account/prices', $data);
    }
}
