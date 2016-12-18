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
        $model = \Permy::getConfig('users_model');
        $user_id = $this->argument('user_id');
        $routes = $this->parseRoutes();
        $operator = $this->option('operator');
        $extra_check = filter_var($this->option('extra_check'), FILTER_VALIDATE_BOOLEAN);

        try {
            $user = $model::findOrFail($user_id);
        } catch (\Exception $e) {
            $this->error("User with ID of $user_id not found");
            return;
        }

        if (\Permy::setUser($user)->can($routes, $operator, $extra_check)) {
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
            ],
        ];
    }
}
