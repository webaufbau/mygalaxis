<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('verification/sendCode', '\App\Controllers\Verification::sendCode');
$routes->post('verification/checkCode', '\App\Controllers\Verification::checkCode');
$routes->post('verification/webhook', '\App\Controllers\Verification::webhook');

$routes->post('form/submit', '\App\Controllers\FluentForm::submit');
