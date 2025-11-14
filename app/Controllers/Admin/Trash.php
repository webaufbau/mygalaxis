<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OfferTrashModel;
use App\Models\UserModel;

class Trash extends BaseController
{
    protected $offerTrashModel;
    protected $userModel;

    public function __construct()
    {
        $this->offerTrashModel = new OfferTrashModel();
        $this->userModel = new UserModel();
    }

    /**
     * Admin Papierkorb - Übersicht aller gelöschten Anfragen
     */
    public function index()
    {
        // Filter-Parameter auslesen
        $type = $this->request->getGet('type') ?? '';
        $platform = $this->request->getGet('platform') ?? '';
        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo = $this->request->getGet('date_to') ?? '';
        $search = $this->request->getGet('search') ?? '';
        $deletedBy = $this->request->getGet('deleted_by') ?? '';

        // Filter aufbauen
        $filters = [];
        if (!empty($type)) $filters['type'] = $type;
        if (!empty($platform)) $filters['platform'] = $platform;
        if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
        if (!empty($dateTo)) $filters['date_to'] = $dateTo;
        if (!empty($search)) $filters['search'] = $search;
        if (!empty($deletedBy)) $filters['deleted_by'] = $deletedBy;

        // Hole gelöschte Anfragen
        $trashedOffers = $this->offerTrashModel->getTrashedOffers($filters);

        // Füge Benutzernamen hinzu für deleted_by
        foreach ($trashedOffers as &$offer) {
            if (!empty($offer['deleted_by_user_id'])) {
                $user = $this->userModel->find($offer['deleted_by_user_id']);
                $offer['deleted_by_username'] = $user ? $user->username : 'Unbekannt';
            } else {
                $offer['deleted_by_username'] = 'System';
            }
        }

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

        // Angebot-Typen
        $offerTypes = [
            'move' => 'Umzug',
            'cleaning' => 'Reinigung',
            'move_cleaning' => 'Umzug + Reinigung',
            'painting' => 'Maler',
            'gardening' => 'Garten',
            'plumbing' => 'Sanitär',
            'electrician' => 'Elektriker',
            'heating' => 'Heizung',
            'tiling' => 'Plattenleger',
            'flooring' => 'Bodenleger',
        ];

        // Alle Admin-User für Filter (vereinfachte Version - hole alle User)
        $adminUsers = $this->userModel
            ->select('id, username')
            ->orderBy('username', 'ASC')
            ->asArray()
            ->findAll();

        return view('admin/trash/index', [
            'trashedOffers' => $trashedOffers,
            'platforms' => $platforms,
            'offerTypes' => $offerTypes,
            'adminUsers' => $adminUsers,
            'filters' => [
                'type' => $type,
                'platform' => $platform,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
                'deleted_by' => $deletedBy,
            ]
        ]);
    }

    /**
     * Detail-Ansicht einer gelöschten Anfrage
     */
    public function view($id)
    {
        $trashedOffer = $this->offerTrashModel->find($id);

        if (!$trashedOffer) {
            return redirect()->to('/admin/trash')->with('error', 'Gelöschte Anfrage nicht gefunden.');
        }

        // Benutzername des Löschenden
        if (!empty($trashedOffer['deleted_by_user_id'])) {
            $user = $this->userModel->find($trashedOffer['deleted_by_user_id']);
            $trashedOffer['deleted_by_username'] = $user ? $user->username : 'Unbekannt';
        } else {
            $trashedOffer['deleted_by_username'] = 'System';
        }

        // Form Fields decodieren
        $trashedOffer['form_fields_decoded'] = !empty($trashedOffer['form_fields'])
            ? json_decode($trashedOffer['form_fields'], true)
            : [];

        // Type-specific data decodieren
        $trashedOffer['type_specific_decoded'] = !empty($trashedOffer['type_specific_data'])
            ? json_decode($trashedOffer['type_specific_data'], true)
            : [];

        return view('admin/trash/view', [
            'offer' => $trashedOffer
        ]);
    }

    /**
     * Optional: Wiederherstellen einer gelöschten Anfrage
     */
    public function restore($id)
    {
        $user = auth()->user();

        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/admin/trash')->with('error', 'Keine Berechtigung.');
        }

        $restored = $this->offerTrashModel->restoreOffer($id);

        if ($restored) {
            return redirect()->to('/admin/trash')->with('success', 'Anfrage wurde erfolgreich wiederhergestellt.');
        } else {
            return redirect()->to('/admin/trash')->with('error', 'Fehler beim Wiederherstellen der Anfrage.');
        }
    }

    /**
     * Endgültiges Löschen aus dem Papierkorb
     */
    public function deletePermanently($id)
    {
        $user = auth()->user();

        if (!$user || !$user->inGroup('admin')) {
            return redirect()->to('/admin/trash')->with('error', 'Keine Berechtigung.');
        }

        $trashedOffer = $this->offerTrashModel->find($id);

        if (!$trashedOffer) {
            return redirect()->to('/admin/trash')->with('error', 'Gelöschte Anfrage nicht gefunden.');
        }

        $deleted = $this->offerTrashModel->delete($id, true);

        if ($deleted) {
            log_message('info', sprintf(
                'Trash entry ID %d permanently deleted by User ID %d',
                $id,
                $user->id
            ));
            return redirect()->to('/admin/trash')->with('success', 'Anfrage wurde endgültig aus dem Papierkorb gelöscht.');
        } else {
            return redirect()->to('/admin/trash')->with('error', 'Fehler beim endgültigen Löschen.');
        }
    }
}
