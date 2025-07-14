<?php

namespace App\Controllers\Admin;

use App\Controllers\AccountBase;
use App\Controllers\Crud;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AdminBase extends AccountBase {
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->template->setHeader('templates/header_account');
        $this->template->setFooter('templates/footer_account');

    }

}
