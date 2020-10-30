<?php

namespace App\Commands\Auth;

use App\Contracts\AuthenticationContract;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Logout extends Command
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
        if (!$auth->check()) {
            $this->info('no authenticated user found');
            return true;
        }
        if($auth->logout()){
            $this->info('user logged out successfully!');
        }

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
