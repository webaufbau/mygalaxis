<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Nutze Shield's auth()-Helper
        if (! auth()->loggedIn()) {
            // Speichere die ursprüngliche URL in der Session
            $session = session();
            $currentUrl = (string) current_url(true);

            // Speichere nur wenn es keine Login/Logout URL ist
            if (!str_contains($currentUrl, '/login') && !str_contains($currentUrl, '/logout')) {
                $session->set('redirect_url', $currentUrl);
                log_message('info', 'AuthFilter: Speichere Redirect-URL: ' . $currentUrl);
            }

            return redirect()->to('/login');
        }

        // Prüfe ob Benutzer blockiert ist
        $user = auth()->user();
        if ($user && $user->is_blocked) {
            auth()->logout();
            return redirect()->to('/login')->with('error', 'Ihr Konto wurde gesperrt. Bitte kontaktieren Sie den Support.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
    }
}
