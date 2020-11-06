<?php

namespace App\Commands\Export;

use App\Commands\BaseCommand;
use App\Contracts\AuthenticationContract;
use App\Contracts\ZipExportContract;
use App\Services\Validation;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\ConnectionException;
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
    private $file_name;

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
        $this->displayDetails($zip);
        if (!$auth->check()) {
           $this->confirm("You are not authenticated, do you want to continue as guest.")
            ? $this->info('Authenticated as guest')
            : $this->call('login');
        }

        $this->multipleTask(
            'Exporting notebook to phpsandbox',
            [
             'Running precompression validations',
             'Compressing files',
             'Running pre upload validation',
             'Uploading notebook',
             'Cleaning up'
            ],
            function () use ($validate){
                if(!$validate->validate(getcwd(),['hasComposer','composerIsValid'])){
                    $this->error(implode("\n",$validate->errors()));
                    return false;
                }
                return true;
            },
            function () use ($zip){
                try {
                    $this->file_name = $zip->compress();
                    return true;
                } catch (ZipException $e){
                    $this->error("Directory could not be compressed.");
                    return false;
                }
            },
            function() use ($validate){
                if (!$validate->validate(getcwd(),["size,$this->file_name"])) {
                    $this->validationError($validate->errors());
                    return false;
                }
                return true;
            },
            function() use ($auth, $zip){
                try {
                    $token =  $auth->retrieveToken();
                    $notebook_details = $zip->upload($this->file_name, $token);
                    $notebook_url = $zip->openNotebook($notebook_details, $token);
                    $this->info(sprintf('your notebook has been provisioned at %s', $notebook_url));
                    return true;
                } catch (RequestException $e) {
                    $this->error($e->getMessage());
                } catch (ConnectionException $e){
                    $this->couldNotConnect();
                }
                return false;
            },
            function() use ($zip){
                $zip->cleanUp();
                return true;
            }
        );

    }

    protected function validationError(array $errors)
    {
         $this->error(implode("\n",$errors));
    }

    protected function couldNotConnect()
    {
        $this->error('Could not establish a connection. Kindly check that your computer is connected to the internet.');
    }

    protected function displayDetails(ZipExportContract $zip)
    {
        $this->line('phpsandbox cli export');
        $content = [
            [
                'Exporting directory',
                getcwd()
            ],
            [
                'Number of files',
                $zip->countFiles(getcwd())
            ]
        ];


        $content = collect($content);
        $this->table([], $content );
    }

    protected function multipleTask()
    {
        $args = func_get_args();
        $this->info( sprintf('%s : starting',$title = $args[0]));
        $taskTitles = $args[1];
        unset($args[0]);
        unset($args[1]);

        foreach (array_values($args) as $key => $task)
        {
            $currentTask = $this->task($taskTitles[$key],$task);
            if ($currentTask !== true){
             $this->info(sprintf('%s : failed',$title));
             exit(1);
            }
        }
        $this->info(sprintf('%s : completed',$title));
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
