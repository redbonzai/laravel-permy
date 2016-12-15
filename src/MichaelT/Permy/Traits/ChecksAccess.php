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
     * @param  mixed (array|string|Illuminate\Routing\Route) $routes
     * @param  string $operator
     * @param  boolean $extra_check
     * @return boolean
    **/
    final public function cant($routes, $operator = 'and', $extra_check = false)
    {
        return $this->logicalUnion(!$this->can($routes), $extra_check, $operator);
    }

    /**
     * Check if the user has permissions for route
     *
     * @param  mixed (array|string|Illuminate\Routing\Route) $routes
     * @param  string $operator
     * @param  boolean $extra_check
     * @return boolean
    **/
    final public function can($routes, $operator = 'and', $extra_check = true)
    {
        $permission = is_array($routes)
            ? $this->canArray($routes)
            : $this->canSingle($routes);

        return $this->logicalUnion($permission, $extra_check, $operator);
    }

    /**
     * Check user permissions for an array of routes
     *
     * @param  array  $routes
     * @return boolean
    **/
    private function canArray(array $routes)
    {
        $permission = false;
        $current_permission = false;

        // Set default logical operator
        $operator = 'and';

        // Validate provided logical operator
        if (array_key_exists('operator', $routes)) {
            $operator = $this->getLogicalOperator($routes['operator']);
            unset($routes['operator']);
        }

        $max = count($routes);

        // Loop through each route and perform a logical operation
        for ($i=0; $i < $max; $i++) {
            $current_permission = $this->canSingle($routes[$i]);

            $permission = ($i == 0)
                ? $current_permission
                : $this->logicalUnion($permission, $current_permission, $operator);
        }

        return $permission;
    }

    /**
     * Check user permissions for a single route
     *
     * @param  mixed (string|Illuminate\Routing\Route) $route
     * @return boolean
    **/
    private function canSingle($route)
    {
        $this->checkUser();

        if (!$route_obj = $this->getRoute($route))
            return false;

        $route_action = $route_obj->getAction();

        // Check if route has a controller
        if (!isset($route_action['controller'])) {
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

        // Parse the route permission
        return $this->parsePermissions($permissions, $controller, $method);
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
        if (!isset(static::$routes))
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
        $route_permission = false;
        $max = count($permissions);

        for ($i=0; $i < $max; $i++) {
            $permission_obj = json_decode($permissions[$i]);

            // Permissions were not set
            if (!isset($permission_obj->{$method})) {
                // If user has only 1 permission set against him - notify and exit immediately
                if ($max == 1)
                    $this->permyNotifyMethodPermissionNotSet($controller, $method);

                break;
            }

            // Permissions were set - carry on with the logic
            $current_permission = (int) $permission_obj->{$method};

            $route_permission = ($i == 0)
                ? $current_permission
                : $this->logicalUnion($route_permission, $current_permission, '');
        }

        return $route_permission;
    }

    /**
     * Perform logical operations on set of permissions
     *
     * @param  boolean $bool1
     * @param  boolean $bool2
     * @param  string  $operator
     * @return string
    **/
    private function logicalUnion($bool1, $bool2, $operator = '')
    {
        $operator = $this->getLogicalOperator($operator);

        if ($operator == 'and')
            return (bool) $bool1 && (bool) $bool2;

        if ($operator == 'or')
            return (bool) $bool1 || (bool) $bool2;

        if ($operator == 'xor')
            return (bool) $bool1 xor (bool) $bool2;
    }

    /**
     * Grab the logical operator
     * return the default one in case if an unsupported operator is provided
     *
     * @return string
    **/
    private function getLogicalOperator($operator = '')
    {
        $available_operators = ['and', 'or', 'xor'];
        $user_operator = $operator ? $operator : \Config::get('laravel-permy::logic_operator');

        return in_array($user_operator, $available_operators)
            ? $user_operator
            : 'and';
    }

    /**
     * Check that the provided user is valid and try setting a default one
     *
     * @return void
    **/
    private function checkUser()
    {
        // bail if not authenticated user or custom user provided
        if (\Auth::guest() && !$this->user)
            throw new PermyUserNotSetException('User is not set');

        // try setting the default user as the authenticated user
        if (!$this->user)
            $this->user = \Auth::user();

        // Get the class to check against
        $model = \Config::get('auth.model');

        // Make sure we're working with a valid object
        if (!$this->user instanceof $model)
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
    public function getUser()
    {
        return $this->user;
    }
}
