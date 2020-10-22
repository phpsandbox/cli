<?php

namespace App\Commands\Export;

use App\Contracts\AuthenticationContract;
use App\Contracts\ZipExportContract;
use App\Services\Validation;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use PhpZip\Exception\ZipException;

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
    public function handle(ZipExportContract $zip , AuthenticationContract $auth ,Validation $validate)
    {
        if (!$auth->check())
        {
           $this->confirm("you are not authenticated , continue as guest")
            ? $auth->setGuest()
            : $auth->login();
        }
        //run pre-compressing validation

        if(!$validate->validate(getcwd(),['hasComposer','composerIsValid'])){
            return $this->validationError($validate->errors());
        }
        try{
            $file_name = $zip->compress();
        }
        catch (ZipException $e)
        {
            return $this->error("directory could not be compressed");
        }

        if(!$validate->validate(getcwd(),["size:$file_name"]))
        {
            return $this->validationError($validate->errors());
        }
       // $zip->upload();

        $zip->cleanUp();








    }

    protected function validationError(array $errors)
    {
        return $this->error(implode("\n",$errors));
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
