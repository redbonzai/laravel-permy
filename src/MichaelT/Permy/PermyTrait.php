<?php

namespace MichaelT\Permy;

/**
 * Checks if the user has permissions for the provided route
 * To be used with the User Model
 *
 * @package michaeltintiuc/laravel-permy
 * @author Michael Tintiuc
 **/
trait PermyTrait
{
    private static $routes;


    /**
     * Define the relationship between the user and permission
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function permy()
    {
        return $this->belongsToMany('MichaelT\Permy\PermyModel', 'permy_user', 'user_id', 'permy_id');
    }

    /**
     * Initiate the routes collection, cache them and find/return the route
     *
     * @param  mixed (string|Illuminate\Routing\Route) $route
     * @return mixed (null|Illuminate\Routing\Route)
     **/
    private function getRouteByName($route)
    {
        // Cache the routes collection
        if ( ! isset(static::$routes))
            static::$routes = \Route::getRoutes();

        // Check if route exists
        return static::$routes->getByName($route);
    }

    /**
     * Check if the user has permissions for route
     *
     * @param  mixed (string|Route) $route
     * @return boolean
    **/
    public function can($route)
    {
        // If $route is not a Route instance, let's try look it up
        $route_obj = $route instanceof \Illuminate\Routing\Route
            ? $route
            : $this->getRouteByName($route);

        if ( ! $route_obj)
            return false;

        $route_action = $route_obj->getAction();
        // Check if route has a controller
        if ( ! isset($route_action['controller']))
        {
            $this->permyNotifyControllerNotSet($route_obj->getUri());
            return false;
        }

        // Get route's controller and method names
        list($controller, $method) = $this->getRouteControllerAndMethod($route_action);

        // Fetch the appropriate user's permission
        try
        {
            $permissions = $this->permy()->select($controller)->first();
        }
        catch (\Exception $e)
        {
            $this->permyNotifyPermissionsNotFound();
            return false;
        }
        // See if permissions were set (controller column exists in DB)
        if ($permissions->{$controller} === null)
        {
            // Restrict access by default
            $this->permyNotifyControllerPermissionNotSet($controller);
            return false;
        }

        try
        {
            // Assume the method permissions were set
            return (bool) json_decode($permissions->{$controller})->{$method};
        }
        catch (\Exception $e)
        {
            $this->permyNotifyMethodPermissionNotSet($controller, $method);
            return false;
        }
    }

    /**
     * Grab the controller and method name from route action
     *
     * @param  array $route_action
     * @return array
    **/
    protected function getRouteControllerAndMethod($route_action)
    {
        $controller_method_arr = explode('@', $route_action['controller']);

        // Convert controller to column name
        $controller_method_arr[0] = \Permy::formatControllerName($controller_method_arr[0]);

        return $controller_method_arr;
    }

    /**
     * Controller is not set for route
     *
     * @param  string $uri
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
     * No permissions were set for $controller in the database. Column does not exist
     *
     * @param  string $controller
     * @return void
    **/
    public function permyNotifyControllerPermissionNotSet($controller)
    {
        //
    }

    /**
     * Update user permissions for $controller@$method. Default to Restricted.
     *
     * @param  string $controller
     * @param  string $method
     * @return void
    **/
    public function permyNotifyMethodPermissionNotSet($controller, $method)
    {
        //
    }
}
