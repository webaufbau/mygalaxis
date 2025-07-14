<?php

if (!function_exists('loadCustomLanguage')) {
    /**
     * Load custom language file if exists, otherwise load the default language file
     *
     * @param string $filename
     * @param string $locale
     * @return array
     */
    function loadCustomLanguage(string $filename, string $locale)
    {
        $defaultPath = APPPATH . "Language/$locale/$filename.php";
        $customPath = WRITEPATH . "Language/$locale/$filename.php";

        if (file_exists($customPath)) {
            return require $customPath;
        }

        if (file_exists($defaultPath)) {
            return require $defaultPath;
        }

        return [];
    }
}
