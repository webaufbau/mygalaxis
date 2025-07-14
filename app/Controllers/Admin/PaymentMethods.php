<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentMethodModel;

class PaymentMethods extends BaseController
{
    protected $paymentMethodModel;

    public function __construct()
    {
        $this->paymentMethodModel = new PaymentMethodModel();
    }

    public function index()
    {
        $methods = $this->paymentMethodModel->findAll();

        return view('admin/payment_methods/index', [
            'methods' => $methods,
            'title' => 'Zahlungsarten verwalten',
            'session' => session(),
        ]);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            $data = [
                'code' => strtolower(trim($this->request->getPost('code'))),
                'name' => trim($this->request->getPost('name')),
                'active' => $this->request->getPost('active') ? 1 : 0,
            ];

            // Validierung
            /* if (empty($data['code']) || empty($data['name'])) {
                return redirect()->back()->with('error', 'Code und Name sind Pflichtfelder')->withInput();
            } */

            // Check unique code
            if ($this->paymentMethodModel->where('code', $data['code'])->first()) {
                return redirect()->back()->with('error', 'Code existiert bereits')->withInput();
            }

            $this->paymentMethodModel->insert($data);
            return redirect()->to('/admin/paymentmethods')->with('message', 'Zahlungsart hinzugefügt');
        }

        return view('admin/payment_methods/create', [
            'title' => 'Neue Zahlungsart anlegen',
            'session' => session(),
        ]);
    }

    public function edit($id)
    {
        $method = $this->paymentMethodModel->find($id);
        if (!$method) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'code' => strtolower(trim($this->request->getPost('code'))),
                'name' => trim($this->request->getPost('name')),
                'active' => $this->request->getPost('active') ? 1 : 0,
            ];

            /* if (empty($data['code']) || empty($data['name'])) {
                return redirect()->back()->with('error', 'Code und Name sind Pflichtfelder')->withInput();
            } */

            // Prüfen auf doppelten Code außer bei aktueller ID
            $existing = $this->paymentMethodModel->where('code', $data['code'])->first();
            if ($existing && $existing['id'] != $id) {
                return redirect()->back()->with('error', 'Code existiert bereits')->withInput();
            }

            $this->paymentMethodModel->update($id, $data);
            return redirect()->to('/admin/paymentmethods')->with('message', 'Zahlungsart aktualisiert');
        }

        return view('admin/payment_methods/edit', [
            'method' => $method,
            'title' => 'Zahlungsart bearbeiten',
            'session' => session(),
        ]);
    }

    public function delete($id)
    {
        $this->paymentMethodModel->delete($id);
        return redirect()->to('/admin/paymentmethods')->with('message', 'Zahlungsart gelöscht');
    }
}
