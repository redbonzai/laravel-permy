<?php

namespace MichaelT\Permy;

use Illuminate\Support\ServiceProvider;

/**
 * Permy ServiceProvider
 *
 * @package michaeltintiuc/laravel-permy
 * @author Michael Tintiuc
 **/
class PermyServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // checking directly through $this->app::VERSION fails in php ~5.5.14
        $app = $this->app;

        if (version_compare($app::VERSION, '5.0.0') == 1) {
            $this->loadTranslationsFrom(__DIR__.'/../../lang', 'laravel-permy');
            $this->mergeConfigFrom( __DIR__.'/../../config/config.php', 'laravel-permy');
            $this->publishes([__DIR__.'/../../config/config.php' => config_path('permy.php')], 'laravel-permy/config.php');
            $this->publishes([__DIR__.'/../../migrations/' => database_path('/migrations')], 'migrations');
        } else {
            $this->package('michaeltintiuc/laravel-permy');
        }

        if ($this->app->runningInConsole())
            $this->commands(['MichaelT\Permy\Commands\Can']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('permy', function()
        {
            return new PermyHandler;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['MichaelT\Permy\PermyHandler'];
    }
}
