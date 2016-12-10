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
    final private function getRoute($route)
    {
        // Cache the routes collection
        if ( ! isset(static::$routes))
            static::$routes = \Route::getRoutes();

        // Check if route exists
        // Check if provided string is action (Controller@method) or a route name
        return (strpos($route, '@') !== false)
            ? static::$routes->getByAction($route)
            : static::$routes->getByName($route);
    }

    /**
     * Check if the user doesn't have permissions for route
     *
     * @param  mixed (string|Illuminate\Routing\Route) $route
     * @return boolean
    **/
    final public function cant($route)
    {
        return !$this->can($route);
    }

    /**
     * Check if the user has permissions for route
     *
     * @param  mixed (string|Illuminate\Routing\Route) $route
     * @return boolean
    **/
    final public function can($route)
    {
        // If $route is not a Route instance, let's try look it up
        $route_obj = $route instanceof \Illuminate\Routing\Route
            ? $route
            : $this->getRoute($route);

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
            $permissions = $this->permy()->lists($controller);
        }
        catch (\Exception $e)
        {
            $this->permyNotifyPermissionsNotFound();
            return false;
        }

        return $this->parsePermissions($permissions, $controller, $method);
    }

    /**
     * Parse the permissions retrieved from database
     *
     * @param  array  $permissions
     * @param  string $controller
     * @param  string $method
     * @return boolean
    **/
    final private function parsePermissions(array $permissions, $controller, $method)
    {
        $max = count($permissions);
        $operator = $this->getOperator();
        $bool_result = 0;

        for ($i=0; $i < $max; $i++)
        {
            $permission_obj = json_decode($permissions[$i]);

            if (isset($permission_obj->{$method}))
            {
                // Permissions were set - perform addition and move on
                $bool_result += (int) $permission_obj->{$method};
                continue;
            }

            // Permissions were not set
            // If user has only 1 permission set against him - notify and exit immediately
            // otherwise carry on and see if other permissions allow access
            if ($max == 1)
            {
                $this->permyNotifyMethodPermissionNotSet($controller, $method);
                break;
            }
        }

        // Calculate the final permission
        // For an AND operator the total boolean value should be equal to permissions length
        // For an OR operator it should be greater than 0
        return $operator == 'and'
            ? $bool_result == $max
            : $bool_result > 0;
    }

    /**
     * Grab the logical operator
     * return the default one in case if an unsupported operator is provided
     *
     * @return string
    **/
    final private function getOperator()
    {
        $available_operators = ['and', 'or'];
        $user_operator = \Config::get('laravel-permy::logic_operator');

        return in_array($user_operator, $available_operators)
            ? $user_operator
            : 'and';
    }

    /**
     * Grab the controller and method name from route action
     *
     * @param  array  $route_action
     * @return array
    **/
    final private function getRouteControllerAndMethod($route_action)
    {
        $controller_method_arr = explode('@', $route_action['controller']);

        // Convert controller to column name
        $controller_method_arr[0] = \Permy::formatControllerName($controller_method_arr[0]);

        return $controller_method_arr;
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
