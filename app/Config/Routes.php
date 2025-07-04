<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes = service('routes');

// Auth-Routen von Shield aktivieren (stellt /login, /register etc. bereit)

$routes->match(['POST'], 'register', 'RegisterController::myregisterAction');
service('auth')->routes($routes);

//$routes->match(['POST'], 'login', 'LoginController::loginAction');

// Homepage-Logik: Weiterleitung je nach Login-Status
$routes->get('/', function () {
    if (auth()->loggedIn()) {
        return redirect()->to('/dashboard');
    }
    return redirect()->to('/login');
});

// Webhooks & Forms
$routes->post('form/webhook', '\App\Controllers\FluentForm::webhook');
$routes->get('form/handle', '\App\Controllers\FluentForm::handle');

// Verifizierung
$routes->get('/verification', 'Verification::index');
$routes->post('/verification/send', 'Verification::send');
$routes->get('/verification/send', 'Verification::send');
$routes->get('/verification/confirm', 'Verification::confirm');
$routes->post('/verification/verify', 'Verification::verify');

$routes->get('/verarbeitung', 'Verification::processing'); // oder beliebiger Pfadname
$routes->get('/verification/check-session', 'Verification::checkSession');
$routes->get('/verification/verify-offer/(:num)/(:any)', 'Verification::verifyOffer/$1/$2');



// Registrierung
$routes->match(['POST'], 'register', 'RegisterController::registerAction');

// ----------------------------
// Geschützter Bereich (Login erforderlich)
// ----------------------------
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');

    $routes->get('filter', 'Filters::index');

    // Offerten
    $routes->get('offers', 'Offers::index');
    $routes->get('offers/(:num)', 'Offers::show/$1');
    $routes->get('offers/create', 'Offers::create');
    $routes->post('offers/store', 'Offers::store');
    $routes->get('offers/edit/(:num)', 'Offers::edit/$1');
    $routes->post('offers/update/(:num)', 'Offers::update/$1');
    $routes->post('offers/delete/(:num)', 'Offers::delete/$1');
    $routes->get('offers/buy/(:num)', 'Offers::buy/$1', ['filter' => 'auth']);

    $routes->group('finance', ['filter' => 'auth'], function($routes) {
        $routes->get('', 'Finance::index');
        $routes->match(['GET', 'POST'], 'topup', 'Finance::topup');
        $routes->get('userpaymentmethods', 'Finance::userPaymentMethods');
        $routes->match(['GET', 'POST'], 'userpaymentmethods/add', 'Finance::addUserPaymentMethod');
        $routes->get('userpaymentmethods/delete/(:num)', 'Finance::deleteUserPaymentMethod/$1');
    });

    // Credits / Guthaben
    $routes->group('credits', function ($routes) {
        $routes->get('/', 'Credit::index');
        $routes->get('add', 'Credit::add');
        $routes->post('add', 'Credit::store');
    });

    $routes->get('agenda', 'AgendaBlock::index', ['filter' => 'auth']);
    $routes->post('agenda/toggle', 'AgendaBlock::toggle', ['filter' => 'auth']);
    $routes->get('agenda/blocked_events', 'AgendaBlock::blocked_events', ['filter' => 'auth']);

    $routes->get('paymentmethods', 'PaymentMethods::index', ['filter' => 'auth']);
    $routes->match(['GET','POST'], 'paymentmethods/create', 'PaymentMethods::create', ['filter' => 'auth']);
    $routes->match(['GET','POST'], 'paymentmethods/edit/(:num)', 'PaymentMethods::edit/$1', ['filter' => 'auth']);
    $routes->get('paymentmethods/delete/(:num)', 'PaymentMethods::delete/$1', ['filter' => 'auth']);

    $routes->get('prices', 'Prices::index', ['filter' => 'auth']);

    $routes->get('reviews', 'Reviews::index', ['filter' => 'auth']);


    // Profil
    $routes->get('profile', 'Profile::index');
    $routes->post('profile/update', 'Profile::update');
});

// ----------------------------
// Admin-Bereich (Admin-Login erforderlich)
// ----------------------------
$routes->group('admin', ['filter' => 'admin-auth'], function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');

    $routes->get('offer/(:num)', 'Admin\Offer::detail/$1');

    // Admin: Credits verwalten
    $routes->group('credits', function ($routes) {
        $routes->get('/', 'Admin\Credit::index');
        $routes->get('create', 'Admin\Credit::create');
        $routes->post('store', 'Admin\Credit::store');
    });

    // Admin: Firmen verwalten
    $routes->get('companies', 'Admin\Company::index');
    $routes->get('companies/create', 'Admin\Company::create');
    $routes->post('companies/store', 'Admin\Company::store');
    $routes->get('companies/edit/(:num)', 'Admin\Company::edit/$1');
    $routes->post('companies/update/(:num)', 'Admin\Company::update/$1');
    $routes->post('companies/delete/(:num)', 'Admin\Company::delete/$1');
});
