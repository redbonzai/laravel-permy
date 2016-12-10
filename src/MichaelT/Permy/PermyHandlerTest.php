<?php

namespace MichaelT\Permy;

use Lang;

/**
 * Lists permissions, updates the language file
 *
 * @package michaeltintiuc/laravel-permy
 * @author Michael Tintiuc
 **/
class PermyHandlerTest extends PermyHandler
{
    /**
     * There was an error updating the permissions file
     *
     * @param Exception $e
     * @return void
     **/
    protected function permyNotifyFileUpdateError(\Exception $e)
    {
        //
    }

    /**
     * A Controller was appended to the permissions file
     *
     * @param string $controller
     * @return void
     **/
    protected function permyNotifyControllerAppended($controller)
    {
        //
    }

    /**
     * A method was appended to a controller in the permissions file
     *
     * @param string $controller
     * @param string $method
     * @return void
     **/
    protected function permyNotifyMethodAppended($controller, $method)
    {
        //
    }
}
