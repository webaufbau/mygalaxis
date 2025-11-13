<?php

// app/Controllers/Auth/RegisterController.php

declare(strict_types=1);

namespace App\Controllers;

//use App\Libraries\IpLimiter;
//use App\Libraries\RateLimiter;
use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegister;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\ValidationException;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Traits\Viewable;
use CodeIgniter\Shield\Validation\ValidationRules;
use DateTime;
use Psr\Log\LoggerInterface;

/**
 * Class RegisterController
 *
 * Handles displaying registration form,
 * and handling actual registration flow.
 */
class RegisterController extends ShieldRegister {
    use ResponseTrait;

    // Add rate limiter property
    protected $rateLimiter;
    protected $ipLimiter;

    public function initController(
        RequestInterface  $request,
        ResponseInterface $response,
        LoggerInterface   $logger
    ): void {
        parent::initController(
            $request,
            $response,
            $logger
        );

        //$this->rateLimiter = new RateLimiter();
        //$this->ipLimiter = new IpLimiter();


        //$ip = $this->request->getIPAddress();
        //echo $ip;
    }

    /**
     * Attempts to register the user.
     */
    public function myregisterAction() {

        // Check if registration is allowed
        if (! setting('Auth.allowRegistration')) {
            // Fehler ausgeben
            session()->setFlashdata('error', lang('Auth.registerDisabled'));
            return view('auth/register');
        }

        /* Spam Protection IF no number in postcode */
        $postcode = $this->request->getPost('company_zip');
        // Überprüfung auf Spam-Anmeldungen
        if (!$postcode || !preg_match('/\d/', $postcode)) {
            session()->setFlashdata('error', lang('Auth.registerDisabled') . 'PLZ');
            return view('auth/register');
        }

        // Honeypot Check
        $honeypot = $this->request->getPost('registerhp');
        if (!empty($honeypot)) {
            session()->setFlashdata('error', lang('Auth.registerDisabled'));
            return view('auth/register');
        }

        /*
        // IP Attempts Check
        $ip = $this->request->getIPAddress();
        if ($this->rateLimiter->tooManyAttempts($ip, 5, 3600)) {
            session()->setFlashdata('error', lang('Auth.registerDisabled'));
            return view('auth/register');
        }

        // IP Banned Check
        $ip = $this->request->getIPAddress();
        if ($this->ipLimiter->isBanned($ip)) {
            session()->setFlashdata('error', lang('Auth.registerDisabled'));
            return view('auth/register');
        }

        // User Agent Banned Check
        $userAgent = $this->request->getUserAgent();
        $bannedUserAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            // Add more user agents to this list as needed
        ];

        if (in_array($userAgent, $bannedUserAgents)) {
            session()->setFlashdata('error', lang('Auth.registerDisabled'));
            return view('auth/register');
        }
        */



            // Check if user is already logged in
            // Redirect to home login
            if (session()->get('user') && is_array(session()->get('user')) && isset(session()->get('user')['id']) && session()->get('user')['id']>0) {
                echo 123;
                exit();
                return redirect()->to(route_to('/'));
            }

            // Proceed with Shield's login attempt
            if (auth()->loggedIn()) {
                echo 3456;
                exit();
                return redirect()->to(config('Auth')->registerRedirect());
            }

            $users = $this->getUserProvider();

            // Validate here first, since some things,
            // like the password, can only be validated properly here.
            $rules = $this->getValidationRules();

            if (! $this->validateData($this->request->getPost(), $rules, [], config('Auth')->DBGroup)) {
                $response = [
                    'validation' => $this->validator,
                    'errors' => $this->validator->getErrors(),
                    'success' => false,
                ];
                session()->setFlashdata('errors', $this->validator->getErrors());

                return view('auth/register', $response);
            }

            // Save the user
            $allowedPostFields = array_keys($rules);
            $user              = new \App\Entities\User();
            $user->fill($this->request->getPost($allowedPostFields));
            $user->setAttribute('email', $this->request->getPost('email'));
            $user->setEmail($this->request->getPost('email'));
            $user->setAttribute('username', $this->request->getPost('email'));

            // Workaround for email only registration/login
            /*if ($user->username === null) {
                $user->username = null;
            }*/

            // Sprachkürzel aus URL-Segment holen, Deutsch hat keinen Prefix
            $locale = service('uri')->getSegment(1);
            $availableLocales = ['en', 'fr', 'it'];
            if (!in_array($locale, $availableLocales)) {
                $locale = 'de'; // Deutsch (default)
            }

            $user->language = $locale;
            $user->setAttribute('language', $locale);

            // Platform aus SiteConfig backendUrl ableiten
            $siteConfig = siteconfig();
            $backendUrl = $siteConfig->backendUrl ?? '';

            // Parse URL und hole Host (z.B. my.offertenschweiz.ch -> my_offertenschweiz_ch)
            $parsedUrl = parse_url($backendUrl);
            $hostname = $parsedUrl['host'] ?? '';
            $platform = str_replace(['.', '-'], '_', $hostname);

            // Fallback: Wenn backendUrl leer oder ungültig, nutze Ordnername
            if (empty($platform)) {
                $rootPath = ROOTPATH; // z.B. /var/www/my_offertenheld_ch/
                $platform = basename(rtrim($rootPath, '/'));
            }

            $user->platform = $platform;
            $user->setAttribute('platform', $platform);

            // Wenn company_email leer ist, setze Login-E-Mail als Standard
            $companyEmail = $this->request->getPost('company_email');
            if (empty($companyEmail)) {
                $user->company_email = $this->request->getPost('email');
                $user->setAttribute('company_email', $this->request->getPost('email'));
            }

            try {
                /*d($this->request->getPost());
                d($this->request->getPost($allowedPostFields));
                dd($user->getAttributes());*/
                $users->save($user);
            } catch (ValidationException $e) {
                $response = [
                    'message' => $users->errors(),
                    'success' => false,
                ];
                return view('auth/register', $response);
            }

            // To get the complete user object with ID, we need to get from the database
            $user = $users->findById($users->getInsertID());

            $db = \Config\Database::connect();
            $zip = $this->request->getPost('company_zip');

            // PLZ-Region suchen
            $siteConfig = siteconfig();
            $siteCountry = $siteConfig->siteCountry ?? null;
            $zipResult = $db->table('zipcodes')
                ->where('country_code', $siteCountry)
                ->where('zipcode', $zip)
                ->get()
                ->getRow();

            if ($zipResult) {
                // Nach dem Speichern des Users und dem Ermitteln von Region/Canton
                $userModel = new \App\Models\UserModel();

                // Filter aus Post-Daten holen
                $postData = $this->request->getPost();

                // Branchen-Kategorien aus Checkboxen
                $filterCategories = isset($postData['filter_categories']) ? implode(',', $postData['filter_categories']) : '';

                // Kantone/Regionen aus PLZ ermitteln
                $region = $zipResult->province ?? '';
                $canton = $zipResult->canton ?? '';

                $userModel->save([
                    'id' => $user->id,
                    'filter_categories' => $filterCategories,
                    'filter_regions' => $region,
                    'filter_cantons' => $canton,
                ]);
            }

            // Add to default group
            $users->addToDefaultGroup($user);

            Events::trigger('register', $user);

            /** @var Session $authenticator */
            $authenticator = auth('session')->getAuthenticator();

            $authenticator->startLogin($user);

            // If an action has been defined for register, start it up.
            $hasAction = $authenticator->startUpAction('register', $user);
            if ($hasAction) {
                redirect()->to(config('Auth')->registerRedirect());
            }

            // Set the user active
            $user->activate();

            $authenticator->completeLogin($user);

            // Success! Redirect to the configured page
            session()->setFlashdata('message', lang('Auth.registerSuccess'));
            return redirect()->to(config('Auth')->registerRedirect());


    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @return array<string, array<string, list<string>|string>>
     */
    protected function getValidationRules(): array {
        /*$rules = new ValidationRules();

        return $rules->getRegistrationRules();*/

        $config = config('Validation');
        $rules = $config->registration;

        return $rules;
    }

}
