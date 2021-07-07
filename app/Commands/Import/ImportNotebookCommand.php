<?php

namespace App\Commands\Import;

use App\Exceptions\HttpException;
use App\Exceptions\InvalidParameterException;
use App\Services\ImportNotebookService;
use App\Traits\FormatHttpErrorResponse;
use App\Traits\Multitask;
use Exception;
use LaravelZero\Framework\Commands\Command;

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

    public function handle(): void
    {
        $importService = new ImportNotebookService($this->argument('notebookId'));

        $this->multiTask('Importing notebook from PHPsandbox', function () use ($importService): void {
            $this->tasks('Verify extraction directory', function () use ($importService) {
                try {
                    $importService->setStorageDirectory($this->option('store'));
                } catch (InvalidParameterException $e) {
                    $this->newLine();
                    $this->error($e->getMessage());

                    return false;
                }
            });

            $this->tasks('Downloading notebook', function () use ($importService) {
                try {
                    $importService->downloadNotebookZip($this->progressBar());
                } catch (HttpException $e) {
                    $this->error($e->getMessage());

                    return false;
                }
            });

            $this->tasks('Extracting notebook', function () use ($importService) {
                try {
                    $importService->extractFiles();
                } catch (Exception $e) {
                    $this->error('An error occurred while extracting the notebook content.');

                    return false;
                }
            });
        });
    }

    public function progressBar()
    {
        return function ($downloadTotal, $downloadedBytes): void {
            if (self::$importStarted) {
                $this->output->progressAdvance((int) $downloadedBytes);
            } else {
                $this->output->newLine();
                $this->output->progressStart((int) $downloadTotal);
                self::$importStarted = true;
            }
        };
    }
}
