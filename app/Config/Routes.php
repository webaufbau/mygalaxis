<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// In Webhook:
$routes->post('verification/webhook', '\App\Controllers\FluentForm::webhook');

// In Custom Url:
$routes->get('form/handle', '\App\Controllers\FluentForm::handle');


$routes->get('/verification', 'Verification::index');
$routes->post('/verification/send', 'Verification::send');
$routes->get('/verification/confirm', 'Verification::confirm');
$routes->post('/verification/verify', 'Verification::verify');
