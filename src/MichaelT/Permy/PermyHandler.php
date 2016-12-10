<?php

namespace MichaelT\Permy;

use Lang;

/**
 * Lists permissions, updates the language file
 *
 * @package michaeltintiuc/laravel-permy
 * @author Michael Tintiuc
 **/
class PermyHandler
{
    private $permissions;
    private $needsUpdate = false;

    public function __construct()
    {
        // Get the initial state of the permissions
        $this->permissions = Lang::get('laravel-permy::permy');

        if( ! is_array($this->permissions))
            $this->permissions = [];
    }

    /**
     * See if we can skip supplied route
     *
     * @param Illuminate\Routing\Route $route
     * @return boolean
     **/
    final private function skip($route)
    {
        // Skip routes that don't use the filter
        if ( ! isset($route->beforeFilters()['permy']))
            return true;

        // Skip routes that don't use controllers
        if ( ! isset($route->getAction()['controller']))
            return true;

        return false;
    }

    /**
     * Get permissions info (pretty name, desc for controllers and their methods)
     * For all registered routes
     *
     * @return array
     **/
    final public function getList()
    {
        foreach (\Route::getRoutes() as $route)
        {
            // See what can be skipped
            if ($this->skip($route))
                continue;

            // Get route's action
            $action = $route->getAction();

            // Get route's controller and method
            list($controller, $method) = explode('@', $action['controller']);

            // format controller class name
            $controller = $this->formatControllerName($controller);

            // Check if we're up to date
            $this->update($controller, $method);
        }

        // Alphabetic A-Z sorting
        ksort($this->permissions);

        return $this->permissions;
    }

    /**
     * Format controller name to represent it's respective column name
     *
     * @param string $controller
     * @return string
     **/
    final public function formatControllerName($controller)
    {
        return strtolower(str_replace(['\\', 'Controller'], ['_', ''], $controller));
    }

    /**
     * Check if the data and the language file need to be updated
     *
     * @param string $controller
     * @param string $method
     * @return void
     **/
    final private function update($controller, $method)
    {
        if ( ! isset($this->permissions[$controller]))
            $this->appendController($controller);

        if ( ! isset($this->permissions[$controller]['methods'][$method]))
            $this->appendMethod($controller, $method);

        $this->updateFile();
    }

    /**
     * Append the controller info array
     *
     * @param string $controller
     * @return void
     **/
    final private function appendController($controller)
    {
        $this->needsUpdate = true;
        $lang_data = ['controller' => $controller];

        $this->permissions[$controller] =
        [
            'name' => Lang::get('permy::defaults.controller.name', $lang_data),
            'desc' => Lang::get('permy::defaults.controller.desc', $lang_data),
        ];

        // Permy language file updated with '$controller' controller. Please set data for it.
        $this->permyNotifyControllerAppended($controller);
    }

    /**
     * Append the method info array to controller
     *
     * @param string $controller
     * @param string $method
     * @return void
     **/
    final private function appendMethod($controller, $method)
    {
        $this->needsUpdate = true;
        $lang_data = ['controller' => $controller, 'method' => $method];

        $this->permissions[$controller]['methods'][$method] =
        [
            'name' => Lang::get('permy::defaults.method.name', $lang_data),
            'desc' => Lang::get('permy::defaults.method.desc', $lang_data),
        ];

        // Permy file does not contain method '$method' for '$controller' controller
        $this->permyNotifyMethodAppended($controller, $method);
    }

    /**
     * See if we need to update the lang file
     *
     * @return void
     **/
    final private function updateFile()
    {
        // return if nothing needs to be updated
        if ( ! $this->needsUpdate)
            return;

        $locale = \App::getLocale();
        $path = app_path()."/lang/packages/$locale/laravel-permy/permy.php";

        try
        {
            // Update permissions language file with new items
            if ( ! \File::put($path, '<?php return '.var_export($this->permissions, true).';'))
                throw new \Exception('Failed to update language file!');
        }
        catch (\Exception $e)
        {
            // Failed to update language file
            $this->permyNotifyFileUpdateError($e);
        }
    }

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
