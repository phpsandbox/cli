<?php

namespace App\Commands\Export;

use App\Contracts\AuthenticationContract;
use App\Contracts\ZipExportContract;
use App\Services\Validation;
use App\Traits\FormatHttpErrorResponse;
use App\Traits\Multitask;
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
    protected $signature = 'export
                            {path? : Path to the project you want to export to PHPSandbox}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Export a directory to PHPSandbox.';

    private $file_name;

    private string $exportDirectory;

    public function __construct()
    {
        parent::__construct();
        $this->exportDirectory = getcwd();
    }

    public function handle(
        ZipExportContract $zip,
        AuthenticationContract $auth,
        Validation $validate
    ): void {

        /**
         * @var $exportDirectory string
         */
        if ($exportDirectory = $this->argument('path')) {
            $this->exportDirectory = $exportDirectory;
        }

        $this->displayDetails();
        $zip->setWorkingDir($this->exportDirectory);

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
                if (! $validate->validate(getcwd(), ["size:$this->file_name"])) {
                    $this->validationError($validate->errors());

                    return false;
                }

                return true;
            });

            $this->tasks('Uploading notebook', function () use ($zip, $auth): bool {
                try {
                    $response = $zip->upload($this->file_name, $token = $auth->retrieveToken());
                    $notebook_url = $zip->openNotebook($response['notebook'], $token);
                    $this->info(sprintf("\n%s You can access your notebook using this link: %s", $response['message'], $notebook_url));

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

    protected function displayDetails(): void
    {
        $this->line('phpsandbox cli export');
        $content = [
            ['Exporting directory', $this->exportDirectory],
            ['Number of files', File::countFiles($this->exportDirectory, config('psb.ignore_files'))],
        ];

        $this->table([], collect($content));
    }
}
