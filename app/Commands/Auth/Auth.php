<?php

namespace App\Commands\Auth;

use App\Contracts\AuthenticationContract;
use App\Services\BrowserService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
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
    protected $description = 'logs in the authenticated user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(AuthenticationContract  $auth)
    {

        $this->task("authenticating", function () use ($auth){
            if(!$auth->check()){
                $auth->setUpNewToken($this);
            }
            $token = $auth->retrieveToken();
            return $auth->tokenIsValid($token)  ? $this->info('authentication was successful')
                                                : $this->error('token could not be validated');
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
