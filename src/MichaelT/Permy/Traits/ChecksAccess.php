<?php

namespace MichaelT\Permy\Traits;

use MichaelT\Permy\Exceptions\PermyUserNotSetException;
use MichaelT\Permy\Exceptions\PermyUserNotModelException;

trait ChecksAccess
{
    private $user;
    private static $routes;

    /**
     * Check if the user doesn't have permissions for route
     *
     * @param  mixed (string|Illuminate\Routing\Route) $route
     * @param  string $operator
     * @param  boolean $extra_check
     * @return boolean
    **/
    final public function cant($route, $operator='and', $extra_check=false)
    {
        return $this->permissionLogicalUnion(!$this->can($route), $extra_check, $operator);
    }

    /**
     * Check if the user has permissions for route
     *
     * @param  mixed (string|Illuminate\Routing\Route) $route
     * @param  string $operator
     * @param  boolean $extra_check
     * @return boolean
    **/
    final public function can($route, $operator='and', $extra_check=true)
    {
        $this->checkUser();

        if ( ! $route_obj = $this->getRoute($route))
            return false;

        $route_action = $route_obj->getAction();

        // Check if route has a controller
        if ( ! isset($route_action['controller'])) {
            $this->permyNotifyControllerNotSet($route_obj->getUri());
            return false;
        }

        // Get route's controller and method names
        list($controller, $method) = $this->getRouteControllerAndMethod($route_action);

        try {
            // Fetch the appropriate user's permission
            $permissions = $this->user->permy()->lists($controller);
        } catch (\Exception $e) {
            $this->permyNotifyPermissionsNotFound();
            return false;
        }

        // Parse the permission to route and handle additional checks
        $permission = $this->parsePermissions($permissions, $controller, $method);

        return $this->permissionLogicalUnion($permission, $extra_check, $operator);
    }

    /**
     * Initiate the routes collection, cache them and find/return the route
     *
     * @param  mixed (string|Illuminate\Routing\Route) $route
     * @return mixed (null|Illuminate\Routing\Route)
     **/
    private function getRoute($route)
    {
        // If $route is a Route instance - return it right away
        if ($route instanceof \Illuminate\Routing\Route)
            return $route;

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
     * Grab the controller and method name from route action
     *
     * @param  array  $route_action
     * @return array
    **/
    private function getRouteControllerAndMethod($route_action)
    {
        $controller_method_arr = explode('@', $route_action['controller']);

        // Convert controller to column name
        $controller_method_arr[0] = $this->formatControllerName($controller_method_arr[0]);

        return $controller_method_arr;
    }

    /**
     * Parse the permissions retrieved from database
     *
     * @param  array  $permissions
     * @param  string $controller
     * @param  string $method
     * @return boolean
    **/
    private function parsePermissions(array $permissions, $controller, $method)
    {
        $final_permission = false;
        $max = count($permissions);

        for ($i=0; $i < $max; $i++) {
            $permission_obj = json_decode($permissions[$i]);

            // Permissions were not set
            if ( ! isset($permission_obj->{$method})) {
                // If user has only 1 permission set against him - notify and exit immediately
                if ($max == 1)
                    $this->permyNotifyMethodPermissionNotSet($controller, $method);

                break;
            }

            // Permissions were set - carry on with the logic
            $current_permission = (int) $permission_obj->{$method};

            $final_permission = $i == 0
                ? $current_permission
                : $this->permissionLogicalUnion($final_permission, $current_permission);
        }

        return $final_permission;
    }

    private function permissionLogicalUnion($final_permission, $current_permission, $operator=null)
    {
        $operator = $this->getOperator($operator);

        if ($operator == 'and')
            return $final_permission && $current_permission;

        if ($operator == 'or')
            return $final_permission || $current_permission;

        if ($operator == 'xor')
            return $final_permission xor $current_permission;
    }

    /**
     * Grab the logical operator
     * return the default one in case if an unsupported operator is provided
     *
     * @return string
    **/
    private function getOperator($operator=null)
    {
        $available_operators = ['and', 'or', 'xor'];
        $user_operator = $operator ? $operator : \Config::get('laravel-permy::logic_operator');

        return in_array($user_operator, $available_operators)
            ? $user_operator
            : 'and';
    }

    private function checkUser()
    {
        if (\Auth::guest() && !$this->user)
            throw new PermyUserNotSetException('User is not set');

        if ( ! $this->user)
            $this->user = \Auth::user();

        $model = \Config::get('auth.model');

        if ( ! $this->user instanceof $model)
            throw new PermyUserNotModelException("User is not an instance of $model");
    }

    /**
     * Set the user
     *
     * @return User instance
    **/
    public function setUser($user)
    {
        $this->user = $user;
        $this->checkUser();

        return $this;
    }

    /**
     * Get the user
     *
     * @return User
    **/
    private function getUser()
    {
        return $this->user;
    }
}
