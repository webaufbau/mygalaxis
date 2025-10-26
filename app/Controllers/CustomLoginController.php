<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\LoginController;

class CustomLoginController extends LoginController
{
    /**
     * Überschreibt die loginAction von Shield um Redirect-URL zu berücksichtigen
     */
    public function loginAction(): \CodeIgniter\HTTP\RedirectResponse
    {
        // Rufe die originale loginAction auf
        $response = parent::loginAction();

        // Prüfe ob Login erfolgreich war (kein Error in Session)
        $session = session();

        // Wenn Login erfolgreich und redirect_url in Session vorhanden
        if (auth()->loggedIn() && $session->has('redirect_url')) {
            $redirectUrl = $session->get('redirect_url');
            $session->remove('redirect_url'); // Entferne aus Session

            log_message('info', 'CustomLoginController: Leite nach Login weiter zu: ' . $redirectUrl);

            return redirect()->to($redirectUrl);
        }

        // Ansonsten normale Shield-Weiterleitung
        return $response;
    }

    /**
     * Überschreibt die Magic Link Handling um Redirect-URL zu berücksichtigen
     */
    public function magicLinkVerify(): \CodeIgniter\HTTP\RedirectResponse
    {
        $response = parent::magicLinkVerify();

        $session = session();
        if (auth()->loggedIn() && $session->has('redirect_url')) {
            $redirectUrl = $session->get('redirect_url');
            $session->remove('redirect_url');

            log_message('info', 'CustomLoginController: Leite nach Magic Link weiter zu: ' . $redirectUrl);

            return redirect()->to($redirectUrl);
        }

        return $response;
    }
}
