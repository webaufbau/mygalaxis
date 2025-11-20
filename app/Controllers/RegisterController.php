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
     * Display the registration form and store referral code if present
     */
    public function registerView()
    {
        // Check for referral code in URL
        $refCode = $this->request->getGet('ref');

        if (!empty($refCode)) {
            // Store in cookie for 30 days
            helper('cookie');

            // DDEV-kompatibel: secure=false für lokale Entwicklung
            $isSecure = (ENVIRONMENT === 'production') ? is_https() : false;

            set_cookie([
                'name'   => 'referral_code',
                'value'  => $refCode,
                'expire' => 60 * 60 * 24 * 30, // 30 Tage
                'secure' => $isSecure,
                'httponly' => true, // Nicht per JavaScript zugreifbar
                'samesite' => 'Lax',
                'domain' => '', // Leer = aktuelle Domain
                'path' => '/',
            ]);

            // Auch in Session speichern als Backup
            session()->set('referral_code', $refCode);
            log_message('info', "Referral code stored: Cookie (secure={$isSecure}, 30d) + Session: {$refCode}");
        }

        // Debug: Prüfe ob Cookie gesetzt wurde
        $cookieValue = get_cookie('referral_code');
        if ($cookieValue) {
            log_message('debug', "Referral cookie verified immediately after set: {$cookieValue}");
        }

        // Call parent method to display registration form
        return parent::registerView();
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

            // Generate unique affiliate code for new user
            $affiliateCode = $this->generateUniqueAffiliateCode();
            if ($affiliateCode) {
                $db = \Config\Database::connect();
                $db->table('users')->where('id', $user->id)->update(['affiliate_code' => $affiliateCode]);
                log_message('info', "Generated affiliate code for new user #{$user->id}: {$affiliateCode}");
            }

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

            // Track referral if affiliate code was used
            $this->trackReferral($user);

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

    /**
     * Generate unique affiliate code for new user
     *
     * @return string|null
     */
    private function generateUniqueAffiliateCode(): ?string
    {
        $userModel = new \App\Models\UserModel();
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            // Generate code: Format REF-XXXXX (5 alphanumeric characters)
            $code = 'REF-' . strtoupper(substr(md5(uniqid((string)time(), true)), 0, 5));

            // Check if unique
            $exists = $userModel->where('affiliate_code', $code)->first();

            if (!$exists) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Track referral if affiliate code was used during registration
     *
     * @param User $user
     * @return void
     */
    private function trackReferral($user): void
    {
        // Check for referral code in multiple sources (POST hat höchste Priorität)
        helper('cookie');
        $postCode = $this->request->getPost('referral_code');
        $cookieCode = get_cookie('referral_code');
        $sessionCode = session()->get('referral_code');

        log_message('debug', "Referral tracking for user #{$user->id}: POST='" . ($postCode ?: 'empty') . "', Cookie='" . ($cookieCode ?: 'empty') . "', Session='" . ($sessionCode ?: 'empty') . "'");

        // Priorität: POST > Cookie > Session
        $referralCode = $postCode ?? $cookieCode ?? $sessionCode;

        if (empty($referralCode)) {
            log_message('warning', "No referral code found in POST/Cookie/Session for user #{$user->id}");
            return;
        }

        log_message('info', "Processing referral for user #{$user->id} with code: {$referralCode} (source: " . ($postCode ? 'POST' : ($cookieCode ? 'Cookie' : 'Session')) . ")");

        // Find referrer by affiliate code
        $referralModel = new \App\Models\ReferralModel();
        $referrerId = $referralModel->getUserIdByCode($referralCode);

        if (!$referrerId) {
            log_message('warning', "Invalid referral code used during registration: {$referralCode}");
            session()->remove('referral_code');
            return;
        }

        // Don't allow self-referrals
        if ($referrerId == $user->id) {
            log_message('warning', "Self-referral attempted by user #{$user->id}");
            session()->remove('referral_code');
            return;
        }

        // Create referral entry
        $ipAddress = $this->request->getIPAddress();
        $companyName = $this->request->getPost('company_name');

        $referralId = $referralModel->createReferral(
            $referrerId,
            $referralCode,
            $user->email,
            $companyName,
            $ipAddress,
            $user->id
        );

        if ($referralId) {
            log_message('info', "Referral tracked: User #{$referrerId} referred #{$user->id} (Referral ID: {$referralId})");

            // Send admin email notification
            $this->sendReferralNotification($referralId, $referrerId, $user);

            // Remove referral code from both session and cookie
            session()->remove('referral_code');
            delete_cookie('referral_code');
        } else {
            log_message('error', "Failed to create referral entry for user #{$user->id}");
        }
    }

    /**
     * Send email notification to admin about new referral
     *
     * @param int $referralId
     * @param int $referrerId
     * @param User $newUser
     * @return void
     */
    private function sendReferralNotification(int $referralId, int $referrerId, $newUser): void
    {
        try {
            $referrerModel = new \App\Models\UserModel();
            $referrer = $referrerModel->find($referrerId);

            if (!$referrer) {
                return;
            }

            // Get admin email from site config
            $siteConfig = siteconfig();
            $adminEmail = $siteConfig->email ?? 'admin@offertenschweiz.ch';

            $email = \Config\Services::email();
            $email->setTo($adminEmail);
            $email->setSubject('Neue Weiterempfehlung: ' . ($newUser->company_name ?? $newUser->email));

            $message = "Eine neue Firma wurde über eine Weiterempfehlung registriert:\n\n";
            $message .= "Vermittelt von:\n";
            $message .= "- Firma: " . ($referrer->company_name ?? '-') . "\n";
            $message .= "- E-Mail: " . $referrer->email . "\n\n";
            $message .= "Neue Firma:\n";
            $message .= "- Firma: " . ($newUser->company_name ?? '-') . "\n";
            $message .= "- E-Mail: " . $newUser->email . "\n\n";
            $message .= "Referral-ID: #{$referralId}\n";
            $message .= "IP-Adresse: " . $this->request->getIPAddress() . "\n\n";
            $message .= "Bitte prüfen Sie die Weiterempfehlung im Admin-Bereich:\n";
            $message .= site_url('admin/referrals');

            $email->setMessage($message);
            $email->send();

            log_message('info', "Referral notification email sent to admin for referral #{$referralId}");
        } catch (\Exception $e) {
            log_message('error', "Failed to send referral notification email: " . $e->getMessage());
        }
    }

}
