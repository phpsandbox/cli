<?php

namespace App\Services;

use App\Exceptions\HttpException;
use App\Exceptions\InvalidParameterException;
use App\Http\Client;
use App\Traits\FormatHttpErrorResponse;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;
use PhpZip\ZipFile;

class ImportNotebookService
{
    use FormatHttpErrorResponse;

    private Client $client;

    private string $notebookUniqueId;

    private ZipFile $zipper;

    private ?string $storageDirectory = null;

    private string $workingDir;

    public function __construct(string $notebookUniqueId)
    {
        $this->client = new Client();
        $this->notebookUniqueId = $notebookUniqueId;
        $this->zipper = new ZipFile();
        $this->workingDir = getcwd();
    }

    public function downloadNotebookZip(Closure $progressCallback): void
    {
        try {
            $this->client->downloadNotebook($this->notebookUniqueId, $progressCallback);
        } catch (ConnectionException $e) {
            throw new HttpException('Could not connect to PHPSandbox. Make sure you are connected to the internet.');
        } catch (RequestException $e) {
            if ($e->getCode() == 404) {
                throw new HttpException($this->formatError($e, 'Invalid notebook ID'));
            }

            throw new HttpException($this->formatError($e));
        }
    }

    public function extractFiles(): void
    {
        $this->zipper->openFile($this->zipFileLocation())->extractTo($this->getStorageDirectory());
    }

    private function zipFileLocation(): string
    {
        return sprintf(
            '%s/%s.zip',
            config('psb.files_storage'),
            $this->notebookUniqueId
        );
    }

    public function setStorageDirectory(?string $storageDirectory = null): bool
    {
        if ($storageDirectory == '.' || $storageDirectory === null) {
            $storageDirectory = getcwd();
        }

        if (! is_dir($storageDirectory)) {
            throw new InvalidParameterException('The directory does not exist');
        }

        $this->storageDirectory = tap(sprintf('%s/%s', $storageDirectory, $this->notebookUniqueId), function (string $directory): void {
            if (! is_dir($directory)) {
                mkdir($directory);
            }
        });

        return true;
    }

    public function getStorageDirectory(): ?string
    {
        return $this->storageDirectory;
    }

    public function runComposerInstall(): bool
    {
        exec("cd {$this->getStorageDirectory()} && composer install", $output, $exitCode);

        return $exitCode == 0;
    }

    public function cleanUp(): void
    {
        /** Return back to the current working directory */
        exec("cd $this->workingDir");

        /** Delete the downloaded zip file */
        File::delete($this->zipFileLocation());
    }
}
