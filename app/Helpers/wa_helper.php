<?php

if (! function_exists('siteconfig')) {
    function siteconfig(): \App\Libraries\SiteConfigLoader
    {
        static $loader = null;
        if ($loader === null) {
            $loader = new \App\Libraries\SiteConfigLoader();
        }
        return $loader;
    }
}
