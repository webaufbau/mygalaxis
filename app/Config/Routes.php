<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('verification/sendCode', 'Verification::sendCode');
$routes->post('verification/checkCode', 'Verification::checkCode');
$routes->post('verification/webhook', 'Verification::webhook');
