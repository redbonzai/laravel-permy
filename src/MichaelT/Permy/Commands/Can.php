<?php

namespace MichaelT\Permy\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Can extends Command
{
    protected $name = 'permy:can';
    protected $description = 'Checks if a user is allowed access to route';

    public function fire()
    {
        $model = \Config::get('auth.model');
        $user_id = $this->argument('user_id');
        $route = $this->argument('route');

        try
        {
            $user = $model::findOrFail($user_id);
        }
        catch (\Exception $e)
        {
            $this->error("User with ID of $user_id not found");
            return;
        }

        if ($user->can($route))
            $this->info('Access is allowed');
        else
            $this->error('Access is restricted');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return
        [
            [
                'user_id',
                InputArgument::REQUIRED,
                'ID of the user to check',
                null
            ],
            [
                'route',
                InputArgument::REQUIRED,
                'Route name or action to check (eg. users.index or Acme\Users\UsersController@index)',
                null
            ],
        ];
    }
}
