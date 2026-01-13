<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EditTokenModel;
use App\Models\OfferModel;
use CodeIgniter\API\ResponseTrait;

class EditToken extends BaseController
{
    use ResponseTrait;

    /**
     * Get offer data by edit token
     * Called by WordPress to pre-fill form fields
     *
     * GET /api/edit-token/{token}
     */
    public function getOfferData(string $token)
    {
        // CORS Headers für WordPress
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        if ($this->request->getMethod() === 'options') {
            return $this->respond(null, 200);
        }

        $editTokenModel = new EditTokenModel();
        $offerModel = new OfferModel();

        // Token validieren
        $tokenData = $editTokenModel->validateToken($token);

        if (!$tokenData) {
            return $this->failNotFound('Token ungültig oder abgelaufen');
        }

        // Offer laden
        $offer = $offerModel->find($tokenData['offer_id']);

        if (!$offer) {
            return $this->failNotFound('Offerte nicht gefunden');
        }

        // Form-Felder aus JSON dekodieren (form_fields enthält alle Formularfelder)
        $formFields = [];
        if (!empty($offer['form_fields'])) {
            $formFields = json_decode($offer['form_fields'], true) ?? [];
        }

        // Daten für Form-Pre-Fill aufbereiten
        $formData = $this->prepareFormData($offer, $formFields);

        return $this->respond([
            'success' => true,
            'offer_id' => $offer['id'],
            'form_data' => $formData,
            'meta' => [
                'created_at' => $offer['created_at'],
                'type' => $offer['type'],
                'status' => $offer['status'],
            ],
        ]);
    }

    /**
     * Bereite Daten für Form Pre-Fill auf
     * Mapped interne Feldnamen auf FluentForm Feldnamen
     */
    protected function prepareFormData(array $offer, array $formFields): array
    {
        $formData = [];

        // Basis-Felder aus offer Tabelle (DB-Spalten -> Form-Feldnamen)
        $directFields = [
            'firstname' => 'vorname',
            'lastname' => 'nachname',
            'email' => 'email',
            'phone' => 'telefon',
            'zip' => 'plz',
            'city' => 'ort',
        ];

        foreach ($directFields as $dbField => $formField) {
            if (!empty($offer[$dbField])) {
                $formData[$formField] = $offer[$dbField];
            }
        }

        // Alle Formular-Felder aus dem JSON (form_fields)
        foreach ($formFields as $key => $value) {
            // Skip interne Felder
            if (in_array($key, ['session', 'request_session_id', 'form_link', 'form_name', 'uuid', 'lang', 'group_id'])) {
                continue;
            }

            // Arrays beibehalten (Checkboxen etc.)
            $formData[$key] = $value;
        }

        return $formData;
    }

    /**
     * Generate edit URL for an offer
     * Called by admin or finalize page
     *
     * POST /api/edit-token/generate
     */
    public function generate()
    {
        $offerId = $this->request->getPost('offer_id');
        $formUrl = $this->request->getPost('form_url');
        $createdBy = $this->request->getPost('created_by') ?? 'user';

        if (!$offerId || !$formUrl) {
            return $this->fail('offer_id und form_url erforderlich');
        }

        $editTokenModel = new EditTokenModel();
        $editUrl = $editTokenModel->generateEditUrl((int)$offerId, $formUrl, $createdBy);

        return $this->respond([
            'success' => true,
            'edit_url' => $editUrl,
        ]);
    }
}
