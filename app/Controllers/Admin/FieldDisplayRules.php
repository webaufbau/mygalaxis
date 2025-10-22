<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FieldDisplayRuleModel;

class FieldDisplayRules extends BaseController
{
    protected $ruleModel;

    public function __construct()
    {
        $this->ruleModel = new FieldDisplayRuleModel();
    }

    /**
     * Liste aller Field Display Rules
     */
    public function index()
    {
        // Check if user is admin
        if (!session()->get('is_admin')) {
            return redirect()->to('/login')->with('error', 'Zugriff verweigert');
        }

        $data = [
            'title' => 'Field Display Rules',
            'rules' => $this->ruleModel->orderBy('offer_type', 'ASC')
                                       ->orderBy('sort_order', 'ASC')
                                       ->findAll(),
            'pager' => $this->ruleModel->pager,
        ];

        return view('admin/field_display_rules/index', $data);
    }

    /**
     * Zeige Formular zum Erstellen einer neuen Rule
     */
    public function create()
    {
        if (!session()->get('is_admin')) {
            return redirect()->to('/login')->with('error', 'Zugriff verweigert');
        }

        $data = [
            'title' => 'Neue Field Display Rule erstellen',
            'rule' => null,
            'offerTypes' => $this->getOfferTypes(),
        ];

        return view('admin/field_display_rules/form', $data);
    }

    /**
     * Speichere neue Rule
     */
    public function store()
    {
        if (!session()->get('is_admin')) {
            return redirect()->to('/login')->with('error', 'Zugriff verweigert');
        }

        // Validate
        $validationRules = [
            'rule_key' => 'required|max_length[100]',
            'offer_type' => 'required|max_length[50]',
            'label' => 'required|max_length[255]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Check if rule already exists
        $ruleKey = $this->request->getPost('rule_key');
        $offerType = $this->request->getPost('offer_type');

        if ($this->ruleModel->ruleExists($ruleKey, $offerType)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Eine Rule mit diesem Schlüssel existiert bereits für diesen Offer-Type');
        }

        // Parse conditions from JSON
        $conditionsJson = $this->request->getPost('conditions_json');
        $conditions = json_decode($conditionsJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Ungültiges JSON-Format für Bedingungen');
        }

        // Parse fields_to_hide from comma-separated string
        $fieldsToHideStr = $this->request->getPost('fields_to_hide');
        $fieldsToHide = array_map('trim', explode(',', $fieldsToHideStr));

        // Save
        $data = [
            'rule_key' => $ruleKey,
            'offer_type' => $offerType,
            'label' => $this->request->getPost('label'),
            'conditions' => $conditions,
            'fields_to_hide' => $fieldsToHide,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order' => $this->request->getPost('sort_order') ?? 0,
            'notes' => $this->request->getPost('notes'),
        ];

        if ($this->ruleModel->insert($data)) {
            return redirect()->to('/admin/field-display-rules')
                           ->with('success', 'Rule erfolgreich erstellt');
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', 'Fehler beim Speichern der Rule');
    }

    /**
     * Zeige Formular zum Bearbeiten einer Rule
     */
    public function edit($id)
    {
        if (!session()->get('is_admin')) {
            return redirect()->to('/login')->with('error', 'Zugriff verweigert');
        }

        $rule = $this->ruleModel->find($id);

        if (!$rule) {
            return redirect()->to('/admin/field-display-rules')
                           ->with('error', 'Rule nicht gefunden');
        }

        $data = [
            'title' => 'Field Display Rule bearbeiten',
            'rule' => $rule,
            'offerTypes' => $this->getOfferTypes(),
        ];

        return view('admin/field_display_rules/form', $data);
    }

    /**
     * Update Rule
     */
    public function update($id)
    {
        if (!session()->get('is_admin')) {
            return redirect()->to('/login')->with('error', 'Zugriff verweigert');
        }

        $rule = $this->ruleModel->find($id);

        if (!$rule) {
            return redirect()->to('/admin/field-display-rules')
                           ->with('error', 'Rule nicht gefunden');
        }

        // Validate
        $validationRules = [
            'rule_key' => 'required|max_length[100]',
            'offer_type' => 'required|max_length[50]',
            'label' => 'required|max_length[255]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        // Check if rule key changed and already exists
        $ruleKey = $this->request->getPost('rule_key');
        $offerType = $this->request->getPost('offer_type');

        if ($this->ruleModel->ruleExists($ruleKey, $offerType, $id)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Eine Rule mit diesem Schlüssel existiert bereits für diesen Offer-Type');
        }

        // Parse conditions from JSON
        $conditionsJson = $this->request->getPost('conditions_json');
        $conditions = json_decode($conditionsJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Ungültiges JSON-Format für Bedingungen');
        }

        // Parse fields_to_hide from comma-separated string
        $fieldsToHideStr = $this->request->getPost('fields_to_hide');
        $fieldsToHide = array_map('trim', explode(',', $fieldsToHideStr));

        // Update
        $data = [
            'rule_key' => $ruleKey,
            'offer_type' => $offerType,
            'label' => $this->request->getPost('label'),
            'conditions' => $conditions,
            'fields_to_hide' => $fieldsToHide,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order' => $this->request->getPost('sort_order') ?? 0,
            'notes' => $this->request->getPost('notes'),
        ];

        if ($this->ruleModel->update($id, $data)) {
            return redirect()->to('/admin/field-display-rules')
                           ->with('success', 'Rule erfolgreich aktualisiert');
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', 'Fehler beim Aktualisieren der Rule');
    }

    /**
     * Lösche Rule
     */
    public function delete($id)
    {
        if (!session()->get('is_admin')) {
            return redirect()->to('/login')->with('error', 'Zugriff verweigert');
        }

        $rule = $this->ruleModel->find($id);

        if (!$rule) {
            return redirect()->to('/admin/field-display-rules')
                           ->with('error', 'Rule nicht gefunden');
        }

        if ($this->ruleModel->delete($id)) {
            return redirect()->to('/admin/field-display-rules')
                           ->with('success', 'Rule erfolgreich gelöscht');
        }

        return redirect()->to('/admin/field-display-rules')
                       ->with('error', 'Fehler beim Löschen der Rule');
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        if (!session()->get('is_admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Zugriff verweigert']);
        }

        $rule = $this->ruleModel->find($id);

        if (!$rule) {
            return $this->response->setJSON(['success' => false, 'message' => 'Rule nicht gefunden']);
        }

        $newStatus = $rule['is_active'] ? 0 : 1;

        if ($this->ruleModel->update($id, ['is_active' => $newStatus])) {
            return $this->response->setJSON([
                'success' => true,
                'is_active' => $newStatus,
                'message' => 'Status erfolgreich geändert'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Fehler beim Ändern des Status']);
    }

    /**
     * Hole verfügbare Offer-Types
     */
    protected function getOfferTypes(): array
    {
        return [
            'default' => 'Standard (alle Branchen)',
            'gartenbau' => 'Gartenbau',
            'umzug' => 'Umzug',
            'reinigung' => 'Reinigung',
            'maler' => 'Maler',
            'bodenbelag' => 'Bodenbeläge',
            'fensterbau' => 'Fensterbau',
            'heizung' => 'Heizung / Sanitär',
        ];
    }
}
