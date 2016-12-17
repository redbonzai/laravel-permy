<?php
namespace MichaelT\Permy\Traits;

use Lang;
use Route as Router;
use Illuminate\Routing\Route;

trait BuildsPermissions
{
    // Does the permissions file need to be updated?
    private $needsUpdate = false;

    /**
     * Get permissions info (pretty name, desc for controllers and their methods)
     * For all registered routes
     *
     * @return array
     **/
    final public function getList()
    {
        $this->setPermissions();

        foreach (Router::getRoutes() as $route) {
            // See what can be skipped
            if ($this->skip($route))
                continue;

            // Get route's controller and method
            list($controller, $method) = explode('@', $route->getActionName());

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
     * Get the permissions file to check against
     *
     * @return void
     **/
    private function setPermissions()
    {
        // Disable fallback locale for easier localization
        Lang::setFallback('');

        // Get the initial state of the permissions
        $this->permissions = Lang::get('laravel-permy::permy');

        if(!is_array($this->permissions))
            $this->permissions = [];

        // Re-enable the fallback locale
        Lang::setFallback(\Config::get('app.fallback_locale'));
    }

    /**
     * See if we can skip supplied route
     *
     * @param Illuminate\Routing\Route $route
     * @return boolean
     **/
    private function skip(Route $route)
    {
        // Skip routes that don't use controllers
        if (!isset($route->getAction()['controller']))
            return true;

        // Skip routes that don't use supplied filters
        return !$this->parseFilters($route);
    }

    /**
     * Parse route and/or controller routes
     *
     * @param Illuminate\Routing\Route $route
     * @return boolean
     **/
    private function parseFilters(Route $route)
    {
        // Get available and route filters
        $available_filters = array_fill_keys((array) $this->getConfig('filters.fillable'), null);

        // We've got route filters
        if (array_intersect_key($available_filters, $route->beforeFilters()))
            return true;

        // Search for controllers with before filters set
        // Get controller name and method
        list($controller, $method) = explode('@', $route->getActionName());

        try {
            // Throws an exception for controllers whose dependencies are not registered
            $controller_filters = \App::make($controller)->getBeforeFilters();
        } catch (\Exception $e) {
            return false;
        }

        $max = count($controller_filters);

        for ($i=0; $i < $max; $i++) {
            // Check if provided methods are set on the controller
            $key_exists = array_key_exists($controller_filters[$i]['original'], $available_filters);
            $applied_to_method = true;

            // No point in further logic if it's not our filter
            if (!$key_exists)
                return false;

            // is our method whitelisted or blacklisted?
            if (isset($controller_filters[$i]['options']['only']))
                $applied_to_method = in_array($method, (array) $controller_filters[$i]['options']['only']);
            elseif (isset($controller_filters[$i]['options']['except']))
                $applied_to_method = !in_array($method, (array) $controller_filters[$i]['options']['except']);

            if ($applied_to_method)
                return true;
        }

        return false;
    }

    /**
     * Check if the data and the language file need to be updated
     *
     * @param string $controller
     * @param string $method
     * @return void
     **/
    private function update($controller, $method)
    {
        if (!isset($this->permissions[$controller]))
            $this->appendController($controller);

        if (!isset($this->permissions[$controller]['methods'][$method]))
            $this->appendMethod($controller, $method);

        $this->updateFile();
    }

    /**
     * Append the controller info array
     *
     * @param string $controller
     * @return void
     **/
    private function appendController($controller)
    {
        $this->needsUpdate = true;
        $lang_data = ['controller' => $controller];

        $this->permissions[$controller] = [
            'name' => Lang::get('laravel-permy::defaults.controller.name', $lang_data),
            'desc' => Lang::get('laravel-permy::defaults.controller.desc', $lang_data),
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
    private function appendMethod($controller, $method)
    {
        $this->needsUpdate = true;
        $lang_data = ['controller' => $controller, 'method' => $method];

        $this->permissions[$controller]['methods'][$method] = [
            'name' => Lang::get('laravel-permy::defaults.method.name', $lang_data),
            'desc' => Lang::get('laravel-permy::defaults.method.desc', $lang_data),
        ];

        // Permy file does not contain method '$method' for '$controller' controller
        $this->permyNotifyMethodAppended($controller, $method);
    }

    /**
     * See if we need to update the lang file
     *
     * @return void
     **/
    private function updateFile()
    {
        // return if nothing needs to be updated
        if (!$this->needsUpdate)
            return;

        $path = $this->getLangPath();
        $file = "permy.php";

        if (!\File::exists($path.$file))
            \File::makeDirectory($path, $recursive=true);

        try {
            // Update permissions language file with new items
            if (!\File::put($path.$file, '<?php return '.var_export($this->permissions, true).';'))
                throw new \Exception('Failed to update language file!');
        } catch (\Exception $e) {
            // Failed to update language file
            $this->permyNotifyFileUpdateError($e);
        }
    }
}
