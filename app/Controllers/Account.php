<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Account extends AccountBase {

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
    }

    public function index() {

        $this->template->set('page_title', lang('Menu.dashboard'));

        return $this->template->return('account/dashboard');
    }


}
