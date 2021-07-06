<?php

namespace App\Commands\Import;

use App\Exceptions\InvalidParameterException;
use App\Traits\Multitask;
use Exception;
use Illuminate\Http\Client\RequestException;
use App\Services\ImportNotebookService;
use App\Traits\FormatHttpErrorResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class ImportNotebookCommand extends Command
{
    use FormatHttpErrorResponse, Multitask;
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'import
                            {notebookId : The unique ID of the notebook being imported}
                            {--s|store= : The directory where the notebook content should be stored}';

    public static bool $importStarted = false;


    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Import a notebook from PHPSandbox to you local machine';
    private ?string $storageDirectory;


    public function handle()
    {
        $importService = new ImportNotebookService($this->argument('notebookId'), $this->output);

        $this->multiTask("Importing notebook from PHPsandbox", function () use ($importService) {

            $this->tasks("Verify extraction directory", function () use ($importService) {
                try {
                    return $importService->setStorageDirectory($this->option('store'));
                } catch (InvalidParameterException $e) {
                    $this->error("The specified directory does not exist");
                }
                return false;
            });

            $this->tasks("Downloading notebook", function () use ($importService) {
                try {
                    $importService->downloadNotebookZip(function(
                        $downloadTotal,
                        $downloadedBytes,
                    ) {
                        if (self::$importStarted) {
                            $this->output->progressAdvance((int) $downloadedBytes);
                        } else {
                            $this->output->progressStart((int) $downloadTotal);
                            self::$importStarted = true;
                        }
                    });
                    return true;

                } catch (ConnectionException $e) {
                    $this->couldNotConnect();
                } catch (RequestException $e) {
                    $this->error($this->showError($e, "Could not find any notebook with ID {$this->argument('notebookId')}"));
                }
                return false;
            });

            $this->tasks("Extracting notebook", function () use ($importService) {
                try {
                    $importService->extractFiles();
                    return true;
                } catch (Exception $e) {
                    $this->error("An error occurred");
                    return false;
                }
            });
        });
    }

}
