<?php

namespace App\Services;

use App\Exceptions\HttpException;
use App\Exceptions\InvalidParameterException;
use App\Http\Client;
use App\Traits\FormatHttpErrorResponse;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use PhpZip\ZipFile;

class ImportNotebookService
{
    use FormatHttpErrorResponse;

    private Client $client;

    private string $notebookUniqueId;

    private ZipFile $zipper;

    private ?string $storageDirectory;

    public function __construct(string $notebookUniqueId)
    {
        $this->client = new Client();
        $this->notebookUniqueId = $notebookUniqueId;
        $this->zipper = new ZipFile();
    }

    public function downloadNotebookZip(Closure $progressCallback): void
    {
        try {
            $this->client->downloadNotebook($this->notebookUniqueId, $progressCallback);
        } catch (ConnectionException $e) {
            throw new HttpException('Could not connect to PHPSandbox. Make sure you are connected to the internet.');
        } catch (RequestException $e) {
            throw new HttpException($this->formatError($e));
        }
    }

    public function extractFiles(): void
    {
        $this->zipper->openFile($this->zipFileLocation())->extractTo($this->storageDirectory);
    }

    private function zipFileLocation(): string
    {
        return sprintf(
            '%s/%s.zip',
            config('psb.files_storage'),
            $this->notebookUniqueId
        );
    }

    public function setStorageDirectory(?string $storageDirectory): bool
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
}
