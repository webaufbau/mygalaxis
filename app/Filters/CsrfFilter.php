<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\Security\Exceptions\SecurityException;

class CsrfFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $security = service('security');
        try {
            $security->verify($request);
        } catch (SecurityException $e) {
            // Flash-Message setzen
            session()->setFlashdata('error', 'Ungültige Anfrage. Bitte versuchen Sie es erneut.');

            // Zurückleiten
            return redirect()->back();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nichts zu tun
    }
}
