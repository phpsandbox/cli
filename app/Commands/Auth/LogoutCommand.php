<?php

namespace App\Commands\Auth;

use App\Contracts\AuthenticationContract;
use LaravelZero\Framework\Commands\Command;

class LogoutCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logout';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Logout authenticated user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(AuthenticationContract $auth)
    {
        $this->task('Logging out user', function () use ($auth) {
            if (! $auth->check()) {
                $this->info('No authenticated user found');

                return true;
            }

            $auth->logout()
                ? $this->info('User logged out successfully')
                : $this->error('An error occurred');

            return true;
        });
    }
}
