<?php

namespace App\Services;

use App\Contracts\BrowserContract;
use App\Contracts\ZipExportContract;
use App\Exceptions\HttpException;
use App\Http\Client;
use App\Traits\FormatHttpErrorResponse;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;
use PhpZip\Util\Iterator\IgnoreFilesRecursiveFilterIterator;
use PhpZip\ZipFile;
use RecursiveDirectoryIterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Gitignore;

class ZipExportService implements ZipExportContract
{
    use FormatHttpErrorResponse;

    protected array $ignoreFiles = [];

    protected ZipFile $zipper;

    protected string $fileStoragePath;

    private Client $client;

    protected string $gitDir = '.git';

    protected string $path;

    public function __construct()
    {
        $this->zipper = new ZipFile();
        $this->client = new Client();
        $this->ignoreFiles = config('psb.ignore_files');
        $this->setFileStorage();
    }

    protected function setFileStorage(): void
    {
        $this->fileStoragePath = config('psb.files_storage');
    }

    /**
     * @return bool|string
     * @throws \PhpZip\Exception\ZipException
     */
    public function compress()
    {
        return $this->createZip();
    }

    public function setWorkingDir(?string $path = null): ZipExportService
    {
        $this->path = $path ?? getcwd();

        return $this;
    }

    /**
     * @return bool| string
     * @throws \PhpZip\Exception\ZipException
     */
    protected function createZip()
    {
        $directoryIterator = new RecursiveDirectoryIterator($this->getZipPath());

        $ignoreIterator = new IgnoreFilesRecursiveFilterIterator(
            $directoryIterator,
            $this->getExcludedFiles()
        );

        $compressed_file_name = sha1(microtime()) . '.zip';

        $full_file_path = $this->getStoragePath($compressed_file_name);

        $this->zipper
            ->addFilesFromIterator($ignoreIterator)
            ->saveAsFile($this->getStoragePath($compressed_file_name));

        return $full_file_path;
    }

    public function cleanUp(): void
    {
        File::cleanDirectory(config('psb.files_storage'));
    }

    protected function getZipPath(): string
    {
        return $this->path;
    }

    protected function getStoragePath($path = ''): string
    {
        if (! is_dir($this->fileStoragePath)) {
            mkdir($this->fileStoragePath, 0777, true);
        }

        return implode(DIRECTORY_SEPARATOR, [$this->fileStoragePath,$path]);
    }

    /**
     * @return array|mixed|null
     */
    public function upload(string $filepath, string $token = '')
    {
        try {
             if ( $response = $this->client->uploadCompressedFile($filepath, $token)) {
                 return $response;
             }

             throw new HttpException('Could not export project.');
        } catch (ConnectionException $e) {
            throw new HttpException('Could not connect to PHPSandbox');
        } catch (RequestException $e) {
            throw new HttpException($this->formatError($e));
        }
    }

    protected function getNotebookUrl(array $details, $token): string
    {
        return $token == ''
            ? sprintf('%s/n/%s?accessToken=%s', config('psb.base_url'), $details['unique_id'], $details['settings']['accessToken'])
            : sprintf('%s/n/%s', config('psb.base_url'), $details['unique_id']);
    }

    public function openNotebook(array $details, string $token): string
    {
        $browser = app(BrowserContract::class);

        $notebook_url = $this->getNotebookUrl($details, $token);

        $browser->open($notebook_url);

        return $notebook_url;
    }

    protected function getGitIgnoreFiles(): array
    {
        $finder = new Finder();
        $gitIgnoreFiles = [];

        try {
            $getRegex = Gitignore::toRegex(File::get($this->path . DIRECTORY_SEPARATOR . '.gitignore'));

            $file_paths = $finder->in($this->path)->exclude($this->ignoreFiles)->ignoreVCS(true)->name($getRegex);

            foreach (iterator_to_array($file_paths, true) as $file_path) {
                $gitIgnoreFiles[] = $file_path->getRelativePathname();
            }

            return $gitIgnoreFiles;
        } catch (FileNotFoundException $e) {
            return [];
        }
    }

    protected function isGitRepo(): bool
    {
        return File::isDirectory($this->path . DIRECTORY_SEPARATOR . $this->gitDir);
    }

    protected function getExcludedFiles(): array
    {
        return $this->isGitRepo()
            ? array_merge($this->getGitIgnoreFiles(), $this->ignoreFiles)
            : $this->ignoreFiles;
    }
}
