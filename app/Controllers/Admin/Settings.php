<?php

namespace App\Controllers\Admin;

use App\Controllers\Account\Crud;
use App\Libraries\SiteConfigLoader;
use App\Models\AuditLogModel;
use CodeIgniter\Files\File;

class Settings extends AdminBase {

    protected string $url_prefix = 'admin/';

    public function index()
    {
        $loader = new SiteConfigLoader();

        if ($this->request->getMethod() === 'POST') {
            $postData = $this->request->getPost();

            // Alte Werte für Audit-Log speichern
            $oldValues = [];
            foreach ($loader->getFields() as $fieldName => $meta) {
                $oldValues[$fieldName] = $loader->$fieldName;
            }

            // Dynamisch alle file-Felder aus SiteConfig prüfen und hochladen
            foreach ($loader->getFields() as $fieldName => $meta) {
                if (($meta['type'] ?? '') === 'file') {
                    $file = $this->request->getFile($fieldName);

                    if ($file && $file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(FCPATH . 'uploads', $newName);
                        $postData[$fieldName] = base_url('uploads/' . $newName);
                    } else {
                        // Wenn keine neue Datei hochgeladen wurde, alten Wert behalten
                        $postData[$fieldName] = $loader->$fieldName;
                    }

                    // Falls "löschen" Checkbox gesetzt
                    if ($this->request->getPost('delete_'.$fieldName)) {
                        $postData[$fieldName] = null;
                    }
                }
            }

            $success = $loader->save($postData);

            if ($success) {
                // Audit-Log: Änderungen protokollieren
                AuditLogModel::logChanges(
                    'settings_update',
                    'settings',
                    null,
                    $oldValues,
                    $postData
                );

                session()->setFlashdata('success', 'Einstellungen gespeichert.');
            } else {
                session()->setFlashdata('error', 'Fehler beim Speichern.');
            }

            return redirect()->to(current_url());
        }

        // Für View: Aktuelle Werte und Metadaten für Felder
        $data = [
            'config' => $loader,
            'fields' => $loader->getFields(),
            'fieldGroups' => $loader->getFieldGroups(),
            'values' => $loader, // $loader hat magic getter, also $values[$key] funktioniert als $loader->$key
            'errors' => session()->getFlashdata('errors'),
            'success' => session()->getFlashdata('success'),
        ];

        return view('admin/settings_form', $data);
    }


    /**
     * @throws \ReflectionException
     */
    protected function uploadFile($field_name): array
    {
        $validationRule = [
            $field_name => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded['.$field_name.']',
                    'is_image['.$field_name.']',
                    'mime_in['.$field_name.',image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size['.$field_name.',1000]',
                    'max_dims['.$field_name.',2000,2000]',
                ],
            ],
        ];

        if (! $this->validate($validationRule)) {
            log_action('Fehler durch Einstellungen Bildupload', json_encode($this->validator->getErrors()));

            return ['errors' => $this->validator->getErrors()];
        }

        $img = $this->request->getFile($field_name);

        if (! $img->hasMoved()) {
            $filepath = WRITEPATH . 'uploads/' . $img->store('');
            log_action('Einstellungen Bildupload konnte nicht verschoben werden', json_encode($img->getPathname()));

            return ['uploaded_fileinfo' => new File($filepath)];
        }

        log_action('Einstellungen Bildupload erfolgreich', json_encode($img->getFilename()));

        return ['errors' => 'The file has already been moved.'];
    }

}
