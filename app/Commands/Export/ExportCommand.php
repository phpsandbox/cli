<?php

namespace App\Commands\Export;

use App\Commands\Concerns\FormatHttpErrorResponse;
use App\Commands\Concerns\Multitask;
use App\Contracts\AuthenticationContract;
use App\Contracts\ZipExportContract;
use App\Services\Validation;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use PhpZip\Exception\ZipException;

class ExportCommand extends Command
{
    use Multitask, FormatHttpErrorResponse;

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
        $zip->setWorkingDir(getcwd());

        if (! $auth->check()) {
            $this->confirm('You are not authenticated, do you want to continue as guest?')
            ? $this->info('Authenticated as guest')
            : $this->call('login');
        }

        $this->multiTask('Exporting project to phpsandbox', function () use ($auth, $zip, $validate): void {
            $this->tasks('Running notebook pre-compression validation', function () use ($validate) {
                if (! $validate->validate(getcwd(), ['hasComposer','composerIsValid'])) {
                    $this->error(implode("\n", $validate->errors()));

                    return false;
                }

                return true;
            });

            $this->tasks('Compressing files', function () use ($zip): bool {
                try {
                    $this->file_name = $zip->compress();

                    return true;
                } catch (ZipException $e) {
                    $this->error('Directory could not be compressed.');

                    return false;
                }
            });

            $this->tasks('Running pre upload validation', function () use ($validate): bool {
                if (! $validate->validate(getcwd(), ["size,$this->file_name"])) {
                    $this->validationError($validate->errors());

                    return false;
                }

                return true;
            });

            $this->tasks('Uploading notebook', function () use ($zip, $auth): bool {
                try {
                    $notebook_details = $zip->upload($this->file_name, $token = $auth->retrieveToken());
                    $notebook_url = $zip->openNotebook($notebook_details, $token);
                    $this->info(sprintf("\nYour notebook has been provisioned at %s", $notebook_url));

                    return true;
                } catch (RequestException $e) {
                    $this->error($this->showError($e));
                } catch (ConnectionException $e) {
                    $this->couldNotConnect();
                }
                $zip->cleanUp();

                return false;
            });

            $this->tasks('Cleaning up', function () use ($zip) {
                $zip->cleanUp();

                return true;
            });
        });
    }

    protected function displayDetails(ZipExportContract $zip): void
    {
        $this->line('phpsandbox cli export');
        $content = [
            ['Exporting directory', getcwd()],
            ['Number of files', File::countFiles(getcwd(), config('psb.ignore_files'))],
        ];

        $this->table([], collect($content));
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
