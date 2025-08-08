<?php

function slugify($text, string $divider = '-')
{
    // replace non letter or digits by divider
    $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, $divider);

    // remove duplicate divider
    $text = preg_replace('~-+~', $divider, $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

function slug_url($module, $module_uid) {
    $seo_model = new \App\Models\SeoModel();
    $seo = $seo_model->where(['module' => $module, 'module_uid' => $module_uid])->first(); // , 'sid' => session('shop_id')

    if(!$seo) {
        // return default
        return site_url($module.'/'.$module_uid);
    }
    return site_url($seo->slug);
}

function domain_url($domain=null, $path='') {
    if(is_null($domain)) {
        $domain = base_url();
    }
    return $domain . str_replace(base_url(), "", site_url($path));
}

if (!function_exists('generate_url')) {
    function generate_url($new_params = []) {
        $request = \Config\Services::request();

        // Aktuelle GET-Parameter abrufen
        $params = $request->getGet();

        // Neue Parameter hinzufügen oder überschreiben
        foreach ($new_params as $key => $value) {
            $params[$key] = $value;
        }

        // Basis-URL erstellen
        $currentPath = $request->getUri()->getPath();
        $url = base_url($currentPath);

        // Neue URL mit GET-Parametern erstellen
        return $url . '?' . http_build_query($params);
    }
}

function lang_url(string $path = '', ?string $locale = null): string
{
    $locale = $locale ?? service('request')->getLocale();

    // Standard-Sprache ohne Prefix
    if ($locale === 'de') {
        return '/' . ltrim($path, '/');
    }

    return '/' . $locale . '/' . ltrim($path, '/');
}
