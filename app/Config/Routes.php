<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes = service('routes');

// Homepage-Logik: Weiterleitung je nach Login-Status
$routes->get('/', function () {
    if (auth()->loggedIn()) {
        return redirect()->to('/dashboard');
    }
    return redirect()->to('/login');
});

function defineAppRoutes($routes) {
    $routes->get('magic-link', 'CustomMagicLinkController::loginView');
    $routes->post('magic-link', 'CustomMagicLinkController::loginAction');
    $routes->get('verify-magic-link', 'CustomMagicLinkController::verify');
    $routes->get('magic-link/verify/(:segment)', 'CustomMagicLinkController::verify/$1');

    // Auth-Routen von Shield aktivieren (stellt /login, /register etc. bereit)

    $routes->match(['POST'], 'register', 'RegisterController::myregisterAction');

    service('auth')->routes($routes);

    // Company Login
    $routes->get('login', '\CodeIgniter\Shield\Controllers\LoginController::loginView');
    $routes->post('login', '\CodeIgniter\Shield\Controllers\LoginController::loginAction');

    // Admin Login (separate URL)
    $routes->get('admin/login', 'Auth::adminLoginView', ['as' => 'admin-login']);
    $routes->post('admin/login', 'Auth::adminLoginAttempt', ['as' => 'admin-login-attempt']);

    // Register
    $routes->get('register', '\CodeIgniter\Shield\Controllers\RegisterController::registerView');
    //$routes->post('register', '\CodeIgniter\Shield\Controllers\RegisterController::registerAction');


    // Passwort zurücksetzen (Reset Password)
    //$routes->get('forgot-password', '\CodeIgniter\Shield\Controllers\ForgotPasswordController::index');
    //$routes->post('forgot-password', '\CodeIgniter\Shield\Controllers\ForgotPasswordController::sendResetLink');
    //$routes->get('reset-password/(:segment)', '\CodeIgniter\Shield\Controllers\ResetPasswordController::index/$1');
    //$routes->post('reset-password', '\CodeIgniter\Shield\Controllers\ResetPasswordController::reset');

    // E-Mail Verifizierung
    //$routes->get('verify-email', '\CodeIgniter\Shield\Controllers\VerifyEmailController::index');
    //$routes->get('verify-email/resend', '\CodeIgniter\Shield\Controllers\VerifyEmailController::resend');

    // Abmelden (Logout)
    $routes->get('logout', '\CodeIgniter\Shield\Controllers\LoginController::logoutAction');

    //$routes->match(['POST'], 'login', 'LoginController::loginAction');

    // ---- ALLE deine bestehenden Routen ab hier ----

    // Webhooks & Forms
    $routes->group('webhook', function($routes) {
        $routes->post('fluentform', '\App\Controllers\FluentForm::webhook');
        $routes->get('fluentform/handle', '\App\Controllers\FluentForm::handle');
        $routes->post('payrexx', '\App\Controllers\WebhookController::payrexx');
        $routes->post('saferpay/notify', '\App\Controllers\WebhookController::saferpayNotify');
        $routes->post('deploy', '\App\Controllers\Deploy::webhook');
    });

    // old:
    $routes->post('form/webhook', '\App\Controllers\FluentForm::webhook');
    $routes->get('form/handle', '\App\Controllers\FluentForm::handle');
    $routes->get('form/session-data', '\App\Controllers\FluentForm::sessionData');





    // Verifizierung
    $routes->get('verification', 'Verification::index');
    $routes->post('verification/send', 'Verification::send');
    $routes->get('verification/send', 'Verification::send');
    $routes->get('verification/confirm', 'Verification::confirm');
    $routes->post('verification/verify', 'Verification::verify');

    $routes->get('processing', 'Verification::processing'); // oder beliebiger Pfadname
    $routes->get('verification/check-session', 'Verification::checkSession');
    $routes->get('verification/verify-offer/(:num)/(:any)', 'Verification::verifyOffer/$1/$2');
    $routes->get('verification/sms-status', 'Verification::checkSmsStatus');


    // Registrierung
    $routes->match(['POST'], 'register', 'RegisterController::registerAction');


    $routes->get('offer/interested/(:segment)', 'PublicController::interestedCompanies/$1');
    $routes->get('rating/write/(:segment)', 'PublicController::showRatingForm/$1');
    $routes->post('rating/submit', 'PublicController::submitRating');
    $routes->get('company/ratings/(:segment)', 'PublicController::companyRatings/$1');

    $routes->get('live-ticker.js', 'LiveTicker::js');

}

defineAppRoutes($routes);


// Gruppe für Sprachen
$routes->group('{locale}', function($routes) {
    defineAppRoutes($routes);
});


$routes->get('api/offers', '\App\Controllers\Api\Offers::index');


$routes->get('test/testtwilio', '\App\Controllers\Test::testtwilio');
$routes->get('test/verification/(:any)', '\App\Controllers\Test::testVerification/$1');



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
        $routes->get('invoice/(:num)', 'Finance::invoice/$1');
        $routes->get('monthly-invoice/(:num)/(:num)', 'Finance::monthlyInvoice/$1/$2');
        $routes->match(['GET', 'POST'], 'topup', 'Finance::topup');
        $routes->match(['GET', 'POST'], 'startAddPaymentMethodAjax', 'Finance::startAddPaymentMethodAjax');
        $routes->get('pdf', 'Finance::pdf');
    });

    $routes->get('finance/startAddPaymentMethod', 'Finance::startAddPaymentMethod');
    $routes->get('finance/paymentSuccess', 'Finance::paymentSuccess');
    $routes->get('finance/paymentCancel', function () {
        return redirect()->to('/finance/userpaymentmethods')->with('error', 'Zahlung wurde abgebrochen.');
    });

    // Auflade-Seite
    $routes->get('finance/topup-page', 'Finance::topupPage', ['filter' => 'auth']);

    // Saferpay Rückleitungen (außerhalb von auth-Filter!)
    $routes->get('finance/topupSuccess', 'Finance::topupSuccess');
    $routes->get('finance/topupFail', 'Finance::topupFail');



    // Credits / Guthaben
    /*$routes->group('credits', function ($routes) {
        $routes->get('/', 'Credit::index');
        $routes->get('add', 'Credit::add');
        $routes->post('add', 'Credit::store');
    });*/

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

// Campaign Übersicht / Liste (GET + POST)
    $routes->match(['GET', 'POST'], 'regions', 'Admin\Regions::index', ['filter' => 'auth']);

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

    
    $routes->get('language-editor', 'Admin\Language::index');
    $routes->post('language-editor/update', 'Admin\Language::update');
    $routes->get('language-editor/search', 'Admin\Language::search');

    $routes->match(['GET', 'POST'], 'push', 'Admin\Push::index');
    $routes->match(['GET', 'POST'], 'push/form', 'Admin\Push::form');
    $routes->match(['GET', 'POST'], 'push/form/(:num)', 'Admin\Push::form/$1');
    $routes->match(['GET', 'POST'], 'push/delete/(:num)', 'Admin\Push::delete/$1');


});
