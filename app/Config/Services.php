<?php

namespace Config;

use App\Language\CustomLanguage;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Pager service.
     *
     * @param bool $getShared
     *
     * @return object
     */
    public static function pager(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('pager');
        }

        // Instantiate your custom Pager class with required parameters
        $config = config(\Config\Pager::class); // Assuming PagerConfig is the configuration class for your Pager
        $view = \Config\Services::renderer(); // Get the RendererInterface instance
        return new Pager($config, $view);
    }

    public static function language(string $locale = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('language', $locale);
        }

        return new CustomLanguage($locale ?? \Config\Services::request()->getLocale());
    }
}
