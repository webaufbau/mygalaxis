<?php

namespace App\Controllers\Admin;

class EmailFieldTranslations extends AdminBase
{
    protected string $translationsPath;

    public function __construct()
    {
        $this->translationsPath = WRITEPATH . 'data/email_field_translations.json';
    }

    /**
     * Zeige/Bearbeite Übersetzungen
     */
    public function index()
    {
        if (!auth()->user()->inGroup('superadmin', 'admin')) {
            return redirect()->to('/')->with('error', 'Keine Berechtigung');
        }

        // POST: Speichern
        if ($this->request->getMethod() === 'POST') {
            $translations = [
                'en' => $this->request->getPost('translation_en') ?: '',
                'fr' => $this->request->getPost('translation_fr') ?: '',
                'it' => $this->request->getPost('translation_it') ?: '',
            ];

            // Erstelle Verzeichnis falls nicht vorhanden
            $dir = dirname($this->translationsPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Speichere als JSON
            if (file_put_contents($this->translationsPath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                return redirect()->to('/admin/email-field-translations')->with('success', 'Übersetzungen erfolgreich gespeichert');
            } else {
                return redirect()->back()->with('error', 'Fehler beim Speichern der Übersetzungen');
            }
        }

        // GET: Lade bestehende Übersetzungen
        $translations = ['en' => '', 'fr' => '', 'it' => ''];

        if (file_exists($this->translationsPath)) {
            $loaded = json_decode(file_get_contents($this->translationsPath), true);
            if ($loaded) {
                $translations = array_merge($translations, $loaded);
            }
        }

        return view('admin/email_field_translations/index', [
            'title' => 'E-Mail Feldwerte-Übersetzungen',
            'translations' => $translations,
        ]);
    }
}
