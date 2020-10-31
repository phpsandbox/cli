<?php

namespace App\Commands\Export;

use App\Contracts\AuthenticationContract;
use App\Contracts\ZipExportContract;
use App\Services\Validation;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\RequestException;
use LaravelZero\Framework\Commands\Command;
use PhpZip\Exception\ZipException;

class ZipExportCommand extends Command
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
     * @param ZipExportContract $zip
     * @param AuthenticationContract $auth
     * @param Validation $validate
     * @return mixed
     */
    public function handle(
        ZipExportContract $zip,
        AuthenticationContract $auth,
        Validation $validate
    ) {
        if (!$auth->check()) {
           $this->confirm("you are not authenticated , continue as guest")
            ? $auth->setGuest()
            : $this->call('login');
        }
        $this->task('exporting',function() use ($zip, $validate, $auth){
            if(!$validate->validate(getcwd(),['hasComposer','composerIsValid'])){
                return $this->validationError($validate->errors());
            }

            try {
                $file_name = $zip->compress();
            } catch (ZipException $e){
                $this->error("directory could not be compressed");
                return false;
            }

            if (!$validate->validate(getcwd(),["size,$file_name"])) {
                $this->validationError($validate->errors());
                return false;
            }  try {
                $token =  $auth->retrieveToken();
                $notebook_details = $zip->upload($file_name, $token);

                $zip->openNotebook($notebook_details, $token);
            } catch (RequestException $e) {
                if ($e->getCode() == 422){
                    $this->error($e->getMessage());
                }
            }

            $zip->cleanUp();
        });
    }

    protected function validationError(array $errors)
    {
         $this->error(implode("\n",$errors));
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
