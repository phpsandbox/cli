<?php

namespace App\Commands;

use App\Contracts\AuthenticationContract;
use App\Contracts\ZipExportContract;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ZipExport extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'export';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Export the current working directory to phpsandbox';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(ZipExportContract $zip , AuthenticationContract $auth)
    {
        if (!$auth->check()){
            $response = $this->call('login');
        }

        $zip->export();
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
