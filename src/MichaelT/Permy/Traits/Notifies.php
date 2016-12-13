<?php

namespace MichaelT\Permy\Traits;

trait Notifies
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

    /**
     * Controller is not set for route
     *
     * @param  string  $uri
     * @return void
    **/
    public function permyNotifyControllerNotSet($uri)
    {
        //
    }

    /**
     * No permissions were set for user
     *
     * @return void
    **/
    public function permyNotifyPermissionsNotFound()
    {
        //
    }

    /**
     * Update user permissions for $controller@$method. Default to Restricted.
     *
     * @param  string  $controller
     * @param  string  $method
     * @return void
    **/
    public function permyNotifyMethodPermissionNotSet($controller, $method)
    {
        //
    }
}
