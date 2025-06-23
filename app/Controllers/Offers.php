<?php
namespace App\Controllers;

use App\Models\OfferModel;
use CodeIgniter\Controller;

class Offers extends Controller
{
    public function index()
    {
        $model = new OfferModel();

        // Optional: Filter & Suche via GET-Parameter
        $search = $this->request->getGet('search');
        $filter = $this->request->getGet('filter'); // z.B. Statusfilter

        $builder = $model->builder();

        if ($search) {
            $builder->like('form_name', $search);
            // Hier kannst du weitere Felder durchsuchen, z.B. 'form_fields'
        }

        if ($filter) {
            $builder->where('status', $filter);
        }

        $offers = $builder->orderBy('created_at', 'DESC')->get()->getResultArray();

        return view('offers/index', [
            'offers' => $offers,
            'search' => $search,
            'filter' => $filter,
            'title' => 'Angebote'
        ]);
    }

    public function buy($id)
    {
        helper('auth');
        $user = auth()->user();

        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer || $offer['status'] !== 'available') {
            return redirect()->to('/offers')->with('error', 'Dieses Angebot kann nicht gekauft werden.');
        }

        // Preis ermitteln (mit evtl. Rabatt)
        $created = new \DateTime($offer['created_at']);
        $now = new \DateTime();
        $days = $now->diff($created)->days;
        $price = $offer['price'];
        if ($days > 3) {
            $price = $price / 2;
        }

        // Hier kannst du Guthaben prüfen oder zur Zahlungsabwicklung weiterleiten
        // Optional: gleich buchen, z.B.:
        $bookingModel = new \App\Models\BookingModel();
        $bookingModel->insert([
            'user_id' => $user->id,
            'type' => 'offer_purchase',
            'description' => "Anfrage gekauft: #" . $id,
            'amount' => -$price,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Status aktualisieren
        $offerModel->update($id, ['status' => 'sold']);

        // E-Mail an Anfrage-Ersteller senden
        service('email')->sendOfferPurchaseMail($offer, $user); // Beispiel-Service

        // E-Mail an Anfragesteller
        $mailer = new \App\Libraries\OfferMailer();
        $mailer->sendOfferPurchasedToRequester($offer, (array)$user);


        return redirect()->to('/offers')->with('message', 'Anfrage erfolgreich gekauft!');
    }

    public function confirm($id)
    {
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($id);

        if (!$offer) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $hash = $this->request->getGet('hash');
        $expectedHash = hash_hmac('sha256', $offer['id'] . $offer['email'], $_ENV['app.secret.key'] ?? 'topsecret');

        if (!hash_equals($expectedHash, $hash)) {
            return redirect()->to('/')->with('error', 'Ungültiger Link.');
        }

        // Auftrag bestätigen...
    }



}
