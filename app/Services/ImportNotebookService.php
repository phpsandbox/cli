<?php


namespace App\Services;


use App\Contracts\ImportNotebookContract;
use App\Exceptions\InvalidParameterException;
use App\Http\Client;
use Closure;
use PhpZip\ZipFile;

class ImportNotebookService
{
    private Client $client;

    private string $notebookUniqueId;

    private ZipFile $zipper;

    private ?string $storageDirectory;


    public function __construct(string $notebookUniqueId, $output)
    {
        $this->client = new Client();
        $this->notebookUniqueId = $notebookUniqueId;
        $this->zipper = new ZipFile();
    }

    public function downloadNotebookZip(Closure $progressCallback)
    {
        $this->client->downloadNotebook($this->notebookUniqueId, $progressCallback);
    }

    public function extractFiles()
    {
        $this->zipper->openFile($this->zipFileLocation())->extractTo($this->storageDirectory);
    }

    private function zipFileLocation(): string
    {
        return sprintf("%s/%s.zip",
            config('psb.files_storage'),
            $this->notebookUniqueId
        );
    }

    public function setStorageDirectory(?string $storageDirectory): bool
    {
        if ($storageDirectory == "."  || is_null($storageDirectory)) {
            $storageDirectory = getcwd();
        }

        if (!is_dir($storageDirectory)) {
            throw new InvalidParameterException("The directory does not exist");
        }

        $this->storageDirectory = tap (sprintf("%s/%s", $storageDirectory, $this->notebookUniqueId), function (string $directory) {
            if (!is_dir($directory)) {
                mkdir($directory);
            }
        });


        return true;
    }


}
