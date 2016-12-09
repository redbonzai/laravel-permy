<?php

namespace MichaelT\Permy;

use Illuminate\Support\Facades\Facade;

/**
 * Permy Facade to leverage the shorthand syntax
 *
 * @package michaeltintiuc/laravel-permy
 * @author Michael Tintiuc
 **/
class PermyFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'permy';
    }
}
