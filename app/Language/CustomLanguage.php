<?php
namespace App\Language;

use CodeIgniter\Language\Language;

class CustomLanguage extends Language
{
    protected function load(string $file, string $locale, bool $return = false)
    {
        $customFilePath = WRITEPATH . "Language/{$locale}/Custom.php";
        $customLang = [];

        // Lade die benutzerdefinierte Sprachdatei, falls vorhanden
        if (is_file($customFilePath)) {
            $customLang = require $customFilePath;
        }

        // Lade die Standard-Sprachdatei
        $standardLang = parent::load($file, $locale, true);

        if(!isset($customLang[$file])) {
            $customLang[$file] = [];
        }

        // Kombiniere die benutzerdefinierte und die Standard-Übersetzungen
        $lang = array_merge($standardLang, $customLang[$file]);

        if ($return) {
            return $lang;
        }

        $this->language[$locale][$file] = $lang;
    }
}
