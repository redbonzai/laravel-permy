<?php
namespace MichaelT\Permy\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Can extends Command
{
    protected $name = 'permy:can';
    protected $description = 'Checks if a user is allowed access to route';

    public function fire()
    {
        $model = $this->option('model');
        $user_id = $this->argument('user_id');
        $routes = $this->parseRoutes();
        $operator = $this->option('operator');
        $roles_logic_operator = $this->option('roles_logic_operator');
        $godmode = filter_var($this->option('godmode'), FILTER_VALIDATE_BOOLEAN);
        $debug = filter_var($this->option('debug'), FILTER_VALIDATE_BOOLEAN);
        $extra_check = filter_var($this->option('extra_check'), FILTER_VALIDATE_BOOLEAN);

        if (\Permy::getConfig('godmode') || $godmode)
            $this->comment('Running in GOD MODE. All route permissions return true.');

        try {
            $user = $model::findOrFail($user_id);
        } catch (\Exception $e) {
            $this->error("User with ID of $user_id not found. Using $model class");
            return;
        }

        $permy = \Permy::setUser($user)
            ->setGodmode($godmode)
            ->setDebug($debug)
            ->setRolesLogicOperator($roles_logic_operator);

        if ($permy->can($routes, $operator, $extra_check)) {
            $this->info('Access is allowed');
            return;
        }

        $this->error('Access is restricted');
    }

    public function parseRoutes()
    {
        $routes = str_replace(', ', ',', $this->argument('routes'));
        return explode(',', $routes);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'user_id',
                InputArgument::REQUIRED,
                'ID of the user to check',
                null
            ], [
                'routes',
                InputArgument::REQUIRED,
                'Single or comma separated route names or actions to check (eg. users.index, users.edit or Acme\Users\UsersController@index)',
                null
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'operator',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Logical operator',
                'and'
            ], [
                'extra_check',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Extra boolean to check',
                true
            ], [
                'model',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Model class to use when fetching a user. Defaults to the class set in permy config',
                \Permy::getConfig('users_model')
            ], [
                'godmode',
                'g',
                InputOption::VALUE_OPTIONAL,
                'Enable or disable GOD MODE. Defaults to value set in permy config',
                \Permy::getConfig('godmode')
            ], [
                'debug',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Enable or disable debugging (Exception throwing). Defaults to value set in permy config',
                \Permy::getConfig('debug')
            ], [
                'roles_logic_operator',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Set logic operator (and, or, xor) when calculating multiple user roles . Defaults to value set in permy config',
                \Permy::getConfig('logic_operator')
            ]
        ];
    }
}
