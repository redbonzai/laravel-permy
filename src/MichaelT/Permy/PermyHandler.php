<?php
namespace MichaelT\Permy;

use MichaelT\Permy\Traits\Notifies;
use MichaelT\Permy\Traits\ChecksAccess;
use MichaelT\Permy\Traits\BuildsPermissions;

/**
 * Lists permissions, updates the language file
 *
 * @package michaeltintiuc/laravel-permy
 * @author Michael Tintiuc
 **/
class PermyHandler
{
    use ChecksAccess, BuildsPermissions, Notifies;

    private $permissions;
    private static $app_version;

    public function __construct()
    {
        $app = app();
        self::$app_version = $app::VERSION;
    }

    /**
     * Format controller name to represent it's respective column name
     *
     * @param string $controller
     * @return string
     */
    final public function formatControllerName($controller)
    {
        return strtolower(str_replace(['\\', 'Controllers', 'Controller'], ['::', 'controllers', ''], $controller));
    }

    /**
     * Get path to language files based on Laravel version
     *
     * @return string
     */
    private function getLangPath()
    {
        $locale = \App::getLocale();

        if (version_compare(self::$app_version, '5.1.0') >= 0)
            return resource_path()."/lang/vendor/laravel-permy/$locale/";
        elseif (version_compare(self::$app_version, '5.0.0') >= 0)
            return resource_path()."/lang/packages/$locale/laravel-permy/";
        else
            return app_path()."/lang/packages/$locale/laravel-permy/";
    }

    /**
     * Get config based on Laravel version
     *
     * @return string
     */
    final public function getConfig($option)
    {
        if (version_compare(self::$app_version, '5.0.0') >= 0)
            return \Config::get("laravel-permy.$option");
        else
            return \Config::get("laravel-permy::$option");
    }
}
