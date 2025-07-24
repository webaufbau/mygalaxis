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
$routes->get('/verification/sms-status', 'Verification::checkSmsStatus');


// Web Hooks
$routes->post('webhooks/payrexx', 'WebhookController::payrexx');

// Registrierung
$routes->match(['POST'], 'register', 'RegisterController::registerAction');


$routes->get('offer/interested/(:segment)', 'PublicController::interestedCompanies/$1');
$routes->get('rating/write/(:segment)', 'PublicController::showRatingForm/$1');
$routes->post('rating/submit', 'PublicController::submitRating');
$routes->get('company/ratings/(:segment)', 'PublicController::companyRatings/$1');




// ----------------------------
// Geschützter Bereich (Login erforderlich)
// ----------------------------
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');

    $routes->get('filter', 'Filters::index');
    $routes->post('filter/save', 'Filters::save');

    // Offerten
    $routes->get('offers', 'Offers::index');
    $routes->get('offers/(:num)', 'Offers::show/$1');
    $routes->get('offers/create', 'Offers::create');
    $routes->post('offers/store', 'Offers::store');
    $routes->get('offers/edit/(:num)', 'Offers::edit/$1');
    $routes->post('offers/update/(:num)', 'Offers::update/$1');
    $routes->post('offers/delete/(:num)', 'Offers::delete/$1');
    $routes->get('offers/buy/(:num)', 'Offers::buy/$1', ['filter' => 'auth']);
    $routes->get('offers/mine', 'Offers::mine');

    $routes->group('finance', ['filter' => 'auth'], function($routes) {
        $routes->get('', 'Finance::index');
        $routes->match(['GET', 'POST'], 'topup', 'Finance::topup');
        $routes->get('userpaymentmethods', 'Finance::userPaymentMethods');
        $routes->match(['GET', 'POST'], 'userpaymentmethods/add', 'Finance::addUserPaymentMethod');
        $routes->match(['GET', 'POST'], 'startAddPaymentMethodAjax', 'Finance::startAddPaymentMethodAjax');
        $routes->get('userpaymentmethods/delete/(:num)', 'Finance::deleteUserPaymentMethod/$1');
    });

    $routes->get('finance/startAddPaymentMethod', 'Finance::startAddPaymentMethod');
    $routes->get('finance/paymentSuccess', 'Finance::paymentSuccess');
    $routes->get('finance/paymentCancel', function () {
        return redirect()->to('/finance/userpaymentmethods')->with('error', 'Zahlung wurde abgebrochen.');
    });

    // Saferpay Rückleitungen (außerhalb von auth-Filter!)
    $routes->get('finance/topupSuccess', 'Finance::topupSuccess');
    $routes->get('finance/topupFail', 'Finance::topupFail');



    // Credits / Guthaben
    $routes->group('credits', function ($routes) {
        $routes->get('/', 'Credit::index');
        $routes->get('add', 'Credit::add');
        $routes->post('add', 'Credit::store');
    });

    $routes->get('agenda', 'AgendaBlock::index', ['filter' => 'auth']);
    $routes->post('agenda/toggle', 'AgendaBlock::toggle', ['filter' => 'auth']);
    $routes->get('agenda/blocked_events', 'AgendaBlock::blocked_events', ['filter' => 'auth']);

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


    $routes->get('user', 'Admin\User::index');
    $routes->match(['GET', 'POST'], 'user/form', 'Admin\User::form');
    $routes->match(['GET', 'POST'], 'user/form/(:num)', 'Admin\User::form/$1');
    $routes->match(['GET', 'POST'], 'user/copy/(:num)', 'Admin\User::copy/$1');
    $routes->match(['GET', 'POST'], 'user/delete/(:num)', 'Admin\User::delete/$1');
    $routes->get('user/json', 'Admin\User::json');

    $routes->match(['GET', 'POST'], 'category', 'Admin\Category::index');
    $routes->match(['GET', 'POST'], 'category/form', 'Admin\Category::form');
    $routes->match(['GET', 'POST'], 'category/form/(:num)', 'Admin\Category::form/$1');
    $routes->match(['GET', 'POST'], 'category/copy/(:num)', 'Admin\Category::copy/$1');
    $routes->match(['GET', 'POST'], 'category/delete/(:num)', 'Admin\Category::delete/$1');

    $routes->match(['GET', 'POST'], 'review', 'Admin\Review::index');
    $routes->match(['GET', 'POST'], 'review/form', 'Admin\Review::form');
    $routes->match(['GET', 'POST'], 'review/form/(:num)', 'Admin\Review::form/$1');
    $routes->match(['GET', 'POST'], 'review/copy/(:num)', 'Admin\Review::copy/$1');
    $routes->match(['GET', 'POST'], 'review/approve/(:num)', 'Admin\Review::approve/$1');
    $routes->match(['GET', 'POST'], 'review/disapprove/(:num)', 'Admin\Review::disapprove/$1');
    $routes->match(['GET', 'POST'], 'review/delete/(:num)', 'Admin\Review::delete/$1');

    $routes->match(['GET', 'POST'], 'settings', 'Admin\Settings::index');
    $routes->match(['GET', 'POST'], 'settings/form', 'Admin\Settings::form');
    $routes->match(['GET', 'POST'], 'settings/form/(:num)', 'Admin\Settings::form/$1');

    $routes->match(['GET','POST'], 'paymentmethods', 'Admin\PaymentMethods::index', ['filter' => 'auth']);
    $routes->match(['GET','POST'], 'paymentmethods/create', 'Admin\PaymentMethods::create', ['filter' => 'auth']);
    $routes->match(['GET','POST'], 'paymentmethods/edit/(:num)', 'Admin\PaymentMethods::edit/$1', ['filter' => 'auth']);
    $routes->match(['GET','POST'], 'paymentmethods/delete/(:num)', 'Admin\PaymentMethods::delete/$1', ['filter' => 'auth']);

// Campaign Übersicht / Liste (GET + POST)
    $routes->match(['GET', 'POST'], 'campaign', 'Admin\Campaign::index', ['filter' => 'auth']);

// Campaign Formular (neu / edit) (GET + POST)
    $routes->match(['GET', 'POST'], 'campaign/form', 'Admin\Campaign::form', ['filter' => 'auth']);
    $routes->match(['GET', 'POST'], 'campaign/form/(:num)', 'Admin\Campaign::form/$1', ['filter' => 'auth']);
    $routes->match(['GET', 'POST'], 'campaign/delete/(:num)', 'Admin\Campaign::delete/$1', ['filter' => 'auth']);

// CSV Vorlage herunterladen
    $routes->get('campaign/download-sample-csv', 'Admin\Campaign::downloadCompanySampleCsv', ['filter' => 'auth']);

// CSV Import Formular anzeigen
    $routes->get('campaign/import_csv', 'Admin\Campaign::import_csv', ['filter' => 'auth']);

// CSV Import verarbeiten (POST)
    $routes->post('campaign/import_csv_process', 'Admin\Campaign::import_csv_process', ['filter' => 'auth']);

    $routes->get('campaign/mark-responded/(:num)', 'Admin\Campaign::markResponded/$1', ['filter' => 'auth']);


});
