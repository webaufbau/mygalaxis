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

        // Wenn Login erfolgreich, prüfe ob Benutzer blockiert ist
        if (auth()->loggedIn()) {
            $user = auth()->user();

            // Prüfe ob Benutzer blockiert ist
            if ($user->is_blocked) {
                // Benutzer ausloggen
                auth()->logout();

                log_message('info', 'Blockierter Benutzer versuchte sich einzuloggen: ' . $user->id . ' - ' . $user->company_name);

                return redirect()->to('/login')->with('error', 'Ihr Konto wurde gesperrt. Bitte kontaktieren Sie den Support.');
            }

            // Wenn redirect_url in Session vorhanden
            if ($session->has('redirect_url')) {
                $redirectUrl = $session->get('redirect_url');
                $session->remove('redirect_url'); // Entferne aus Session

                log_message('info', 'CustomLoginController: Leite nach Login weiter zu: ' . $redirectUrl);

                return redirect()->to($redirectUrl);
            }
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
