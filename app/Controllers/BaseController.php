<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Libraries\Template;
/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Instance of Template class
     *
     * @var Template
     */
    protected Template $template;

    protected string $app_controller = '';

    protected string $url_prefix = '';

    protected string $permission_prefix = '';

    protected $siteConfig;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');

        // Preload any models, libraries, etc, here.
        $this->template = new Template();

        //$this->template->set('flash_message', $this->getFlash());

        $siteConfig = config('SiteConfig');
        $this->siteConfig = $siteConfig;
        $this->template->set('siteConfig', $siteConfig);

        $class_name = explode("\\",get_class($this));
        $this->app_controller = strtolower(end($class_name));
        $this->template->set('app_controller', $this->app_controller);

        if(!isset($this->url_prefix)) { $this->url_prefix = 'account'; }

        $this->template->set('url_prefix', $this->url_prefix);

        if(isset($_SERVER['REQUEST_URI'])) {
            $request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
        } else {
            $request_uri = '';
        }
        $this->template->set('queryroute', $request_uri);
        $this->template->set('queryparts', explode("/", $request_uri));

    }

    public function setFlash($message, $type = 'info'): void
    {
        if($type=='error') {$type='danger';}
        session()->setFlashdata($type, $message);
    }

    public function getFlash(): ?array {
        return session()->getFlashdata('error');
    }
}
