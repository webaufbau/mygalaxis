<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// In Webhook:
$routes->post('verification/webhook', '\App\Controllers\Verification::webhook');

// In Custom Url:
$routes->get('form/handle', '\App\Controllers\FluentForm::handle');

// Noch keinen Zweck:
$routes->post('verification/sendCode', '\App\Controllers\Verification::sendCode');
$routes->post('verification/checkCode', '\App\Controllers\Verification::checkCode');
$routes->get('form/submit', '\App\Controllers\FluentForm::submit');
$routes->post('form/submit', '\App\Controllers\FluentForm::submit');
