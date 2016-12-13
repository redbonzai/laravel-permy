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

    /**
     * Format controller name to represent it's respective column name
     *
     * @param string $controller
     * @return string
     **/
    final public function formatControllerName($controller)
    {
        return strtolower(str_replace(['\\', 'Controller'], ['::', ''], $controller));
    }
}
