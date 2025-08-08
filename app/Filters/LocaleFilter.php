<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class LocaleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $availableLocales = ['de', 'en', 'fr', 'it'];

        // Wenn eingeloggt → Sprache aus Profil
        if (auth()->loggedIn()) {
            $userLocale = auth()->user()->language ?? 'de';

            if (in_array($userLocale, $availableLocales)) {
                service('request')->setLocale($userLocale);
                return;
            }
        }

        // Wenn nicht eingeloggt → Sprache aus URL
        $locale = $request->getLocale();
        if (in_array($locale, $availableLocales)) {
            service('request')->setLocale($locale);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
