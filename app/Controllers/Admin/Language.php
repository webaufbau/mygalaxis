<?php

namespace App\Controllers\Account\Admin;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Language extends AdminBase
{

    public function index()
    {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/account');
        }

        $languages = [];
        $languagePath = APPPATH . 'Language/';
        $customLanguagePath = WRITEPATH . 'Language/';

        // Alle verfügbaren Übersetzungsdateien finden
        $languageFiles = glob($languagePath . '*/*.php');

        // Alle verfügbaren Dateien und Sprachen vorbereiten
        foreach ($languageFiles as $file) {
            $filePathParts = explode('/', $file);
            $language = $filePathParts[count($filePathParts) - 2];
            $filename = basename($file, '.php');

            if (!isset($languages[$language])) {
                $languages[$language] = [];
            }

            // Die Standardübersetzungsdatei laden
            $languages[$language][$filename] = require($file);

            // Falls eine benutzerdefinierte Datei existiert, überschreibe die Standardübersetzungen
            $customFilePath = $customLanguagePath . $language . '/Custom.php';
            if (file_exists($customFilePath)) {
                $customLang = require($customFilePath);
                if (isset($customLang[$filename])) {
                    $languages[$language][$filename] = array_merge(
                        $languages[$language][$filename],
                        $customLang[$filename]
                    );
                }
            }
        }

        // Wenn eine Datei ausgewählt wurde, zeigen wir die Felder an
        $selectedFile = $this->request->getGet('file');
        $selectedLanguage = $this->request->getGet('language');
        $translations = [];
        if ($selectedLanguage && $selectedFile) {
            $translations = $languages[$selectedLanguage][$selectedFile] ?? [];
        }

        // Daten für die View bereitstellen
        $this->template->set([
            'languages' => $languages,
            'selectedLanguage' => $selectedLanguage,
            'selectedFile' => $selectedFile,
            'translations' => $translations,
            'search_term' => $this->request->getGet('search_term'),
        ]);

        return $this->template->load('account/admin/language_editor');
    }

    public function update()
    {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/account');
        }

        $postData = $this->request->getPost();
        $languagePath = APPPATH . 'Language/';
        $customLanguagePath = WRITEPATH . 'Language/';

        foreach ($postData as $language => $files) {
            foreach ($files as $filename => $translations) {
                $standardFilePath = $languagePath . $language . '/' . $filename . '.php';
                $standardTranslations = require($standardFilePath);

                // add custom translations to standard translations
                $customFilePath = $customLanguagePath . $language . '/Custom.php';
                if (file_exists($customFilePath)) {
                    $customLang = require($customFilePath);
                    if (isset($customLang[$filename])) {
                        $standardTranslations = array_merge(
                            $standardTranslations,
                            $customLang[$filename]
                        );
                    }
                }

                $customTranslations = $this->getCustomTranslations($standardTranslations, $translations);
                if (!empty($customTranslations)) {
                    $filePath = $customLanguagePath . $language . '/Custom.php';  // Sicherstellen, dass im benutzerdefinierten Ordner gespeichert wird

                    // Erstelle den Ordner, falls er noch nicht existiert
                    if (!is_dir(dirname($filePath))) {
                        mkdir(dirname($filePath), 0777, true);
                    }

                    // Benutzerdefinierte Datei speichern
                    $existingCustomTranslations = file_exists($filePath) ? require($filePath) : [];
                    // $mergedTranslations = array_merge_recursive($existingCustomTranslations, [$filename => $customTranslations]);
                    $mergedTranslations = $this->mergeTranslations($existingCustomTranslations, [$filename => $customTranslations]);
                    $content = "<?php\n\nreturn " . var_export($mergedTranslations, true) . ";\n";
                    file_put_contents($filePath, $content);
                }
            }
        }

        return redirect()->to('/admin/language-editor')->with('success', 'Übersetzungen aktualisiert.');
    }

    public function search() {
        if (!auth()->user()->can('my.'.$this->app_controller.'_view')) {
            return redirect()->to('/account');
        }

        $search_term = $this->request->getGet('search-term');

        $languages = [];
        $languagePath = APPPATH . 'Language/';
        $customLanguagePath = WRITEPATH . 'Language/';

        // Alle verfügbaren Übersetzungsdateien finden
        $languageFiles = glob($languagePath . '*/*.php');

        // Alle verfügbaren Dateien und Sprachen vorbereiten
        foreach ($languageFiles as $file) {
            $filePathParts = explode('/', $file);
            $language = $filePathParts[count($filePathParts) - 2];
            $filename = basename($file, '.php');

            if (!isset($languages[$language])) {
                $languages[$language] = [];
            }

            // Die Standardübersetzungsdatei laden
            $languages[$language][$filename] = require($file);

            // Falls eine benutzerdefinierte Datei existiert, überschreibe die Standardübersetzungen
            $customFilePath = $customLanguagePath . $language . '/Custom.php';
            if (file_exists($customFilePath)) {
                $customLang = require($customFilePath);
                if (isset($customLang[$filename])) {
                    $languages[$language][$filename] = array_merge(
                        $languages[$language][$filename],
                        $customLang[$filename]
                    );
                }
            }
        }




        $searchTerm = $this->request->getGet('search-term');
        $results = $this->searchInFiles($searchTerm, $languagePath);

        // Daten für die View bereitstellen
        $this->template->set([
            'languages' => $languages,
            'searchResults' => $results,
            'search_term' => $search_term,
        ]);

        return $this->template->load('account/admin/language_editor');
    }

    private function searchInFiles($searchTerm, $directory) {
        $result = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $contents = file_get_contents($file->getPathname());
                if (stripos($contents, $searchTerm) !== false) {
                    $result[] = $file->getPathname();
                }
            }
        }
        return $result;
    }

    private function getCustomTranslations($standardTranslations, $newTranslations)
    {
        $customTranslations = [];
        foreach ($newTranslations as $key => $value) {
            if (is_array($value)) {
                $customTranslations[$key] = $this->getCustomTranslations(
                    $standardTranslations[$key] ?? [],
                    $value
                );
                if (empty($customTranslations[$key])) {
                    unset($customTranslations[$key]);
                }
            } elseif (!isset($standardTranslations[$key]) || $standardTranslations[$key] !== $value) {
                $customTranslations[$key] = $value;
            }
        }
        return $customTranslations;
    }

    private function mergeTranslations(array $existing, array $new): array {
        foreach ($new as $filename => $translations) {
            // Wenn der Dateiname bereits existiert, überschreibe nur die Einträge, die sich geändert haben
            if (isset($existing[$filename])) {
                foreach ($translations as $key => $value) {
                    if (isset($existing[$filename][$key]) && $existing[$filename][$key] !== $value) {
                        // Wert überschreiben, wenn der neue Wert anders ist
                        $existing[$filename][$key] = $value;
                    } elseif (!isset($existing[$filename][$key])) {
                        // Neuen Eintrag hinzufügen, wenn der Schlüssel nicht existiert
                        $existing[$filename][$key] = $value;
                    }
                }
            } else {
                // Wenn der Dateiname nicht existiert, füge die gesamten Übersetzungen hinzu
                $existing[$filename] = $translations;
            }
        }

        return $existing;
    }

    private function convertArrayToStrings($array) {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->convertArrayToStrings($value);
            } else {
                $result[$key] = (string) $value;
            }
        }
        return $result;
    }
}
