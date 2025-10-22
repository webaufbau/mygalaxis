<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FormAuditLogModel;

class AuditLog extends BaseController
{
    protected $auditModel;

    public function __construct()
    {
        $this->auditModel = new FormAuditLogModel();
    }

    /**
     * Zeige alle Audit Logs mit Filtern
     */
    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'event_category' => $this->request->getGet('category'),
            'event_type' => $this->request->getGet('type'),
            'platform' => $this->request->getGet('platform'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'limit' => $this->request->getGet('limit') ?? 100,
        ];

        $logs = $this->auditModel->getLogsFiltered($filters);

        return view('admin/audit_log/index', [
            'title' => 'Audit Log',
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    /**
     * Zeige alle Logs für eine bestimmte UUID
     */
    public function byUuid(string $uuid)
    {
        $logs = $this->auditModel->getLogsByUuid($uuid);

        // Hole auch Offer-Daten wenn vorhanden
        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->where('uuid', $uuid)->first();

        return view('admin/audit_log/by_uuid', [
            'title' => 'Audit Log - UUID: ' . $uuid,
            'uuid' => $uuid,
            'logs' => $logs,
            'offer' => $offer,
        ]);
    }

    /**
     * Zeige alle Logs für eine Group ID
     */
    public function byGroupId(string $groupId)
    {
        $logs = $this->auditModel->getLogsByGroupId($groupId);

        // Hole alle Offers in dieser Gruppe
        $offerModel = new \App\Models\OfferModel();
        $offers = $offerModel->where('group_id', $groupId)->findAll();

        return view('admin/audit_log/by_group', [
            'title' => 'Audit Log - Group: ' . $groupId,
            'group_id' => $groupId,
            'logs' => $logs,
            'offers' => $offers,
        ]);
    }

    /**
     * Zeige alle Logs für eine Offer ID
     */
    public function byOfferId(int $offerId)
    {
        $logs = $this->auditModel->getLogsByOfferId($offerId);

        $offerModel = new \App\Models\OfferModel();
        $offer = $offerModel->find($offerId);

        return view('admin/audit_log/by_offer', [
            'title' => 'Audit Log - Offerte #' . $offerId,
            'offer_id' => $offerId,
            'logs' => $logs,
            'offer' => $offer,
        ]);
    }
}
