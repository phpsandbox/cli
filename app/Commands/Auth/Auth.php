<?php

namespace App\Commands\Auth;

use App\Contracts\AuthenticationContract;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Auth extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */

    protected $signature = 'login';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Authenticates a User';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(AuthenticationContract  $auth)
    {
        $this->task("Authenticating", function() use($auth) {
            if(!$auth->check()) {
                $auth->setUpNewToken($this);
            }

            $token = $auth->retrieveToken();

            $auth->tokenIsValid($token)
                ? $this->info('Authentication was successful.')
                : $this->error('Token could not be validated.');
        });
    }


    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
