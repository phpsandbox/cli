<?php

namespace App\Services;

use App\Contracts\BrowserContract;
use App\Contracts\ZipExportContract;
use App\Http\Client;
use Illuminate\Support\Facades\File;
use PhpZip\Exception\ZipException;
use PhpZip\Util\Iterator\IgnoreFilesRecursiveFilterIterator;
use PhpZip\ZipFile;
use RecursiveDirectoryIterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Gitignore;

/**
 * Class ZipExportService
 * @package App\Services
 */
class ZipExportService implements ZipExportContract
{

    /**
     * Directories that should not be included in the zip file
     *
     * @var string[]
     */
    protected  $ignoreFiles = [
                                    'vendor',
                                    'node_modules',
                                    '.git',
                            ];
    /**
     *
     * @var ZipFile
     */
    protected  $zipper;

    protected  $fileStoragePath;
    /**
     * @var Client
     */
    private $client;

    /**
     * ZipExportService constructor.
     */
    public function __construct()
    {
        $this->zipper = new ZipFile();
        $this->client = new Client();
        $this->setFileStorage();
    }

    protected function setFileStorage()
    {
        $this->fileStoragePath = config('psb.files_storage');
    }

    public function countFiles($path)
    {
        $size = 0;
        $ignore = ['vendor','.','.git','..','node_modules','.idea','.phpintel'];
        $files = scandir($path);

        foreach($files as $t) {
            if (in_array($t, $ignore)) {
                continue;
            }

            if (is_dir(rtrim($path, '/') . '/' . $t)) {
                $size += $this->countFiles(rtrim($path, '/') . '/' . $t);
            } else {
                $size++;
            }
        }

        return $size;
    }


    public function compress()
    {
        return $this->createZip();
    }


    protected function createZip()
    {
        $directoryIterator = new RecursiveDirectoryIterator($this->getZipPath());

        $ignoreIterator = new IgnoreFilesRecursiveFilterIterator(
            $directoryIterator,
            $this->getExcludedFiles()
        );

        $compressed_file_name = sha1(microtime()).".zip";

        $full_file_path = $this->getStoragePath($compressed_file_name);

        $this->zipper
            ->addFilesFromIterator($ignoreIterator)
            ->saveAsFile($this->getStoragePath($compressed_file_name));

        return $full_file_path;
    }


    public function cleanUp()
    {
        File::cleanDirectory(config('psb.files_storage'));
    }



    /**
     * Get the path to the directory to be compressed
     *
     * @return string
     */
    protected function getZipPath()
    {
        return getcwd();
    }

    /**
     * Get the path to store the compressed file
     *
     * @param string $path
     * @return false|string
     */
    protected function getStoragePath($path = ''): string
    {
      if (!is_dir($this->fileStoragePath)) {
          mkdir($this->fileStoragePath, 0777, true);
      }

      return implode(DIRECTORY_SEPARATOR,[$this->fileStoragePath,$path]);
    }

    public function upload($filepath, $token = '')
    {
        return $this->client->uploadCompressedFile($filepath, $token);
    }

    protected function getNotebookUrl(array $details, $token)
    {
        return $token == ''
            ? sprintf('%s/n/%s?accessToken=%s', config('psb.base_url'), $details['unique_id'], $details['settings']['accessToken'])
            : sprintf('%s/n/%s', config('psb.base_url'), $details['unique_id']);
    }

    public function openNotebook(array $details, string $token): string
    {
        $browser = app()->make(BrowserContract::class);

        $notebook_url = $this->getNotebookUrl($details, $token);

        $browser->open($notebook_url);

        return $notebook_url;
    }

    protected function getGitIgnoreFiles(): array
    {
        $finder = new Finder();
        $gitIgnoreFiles = [];

        $getRegex = Gitignore::toRegex(File::get(base_path('.gitignore')));

       $file_paths = $finder->in(getcwd())->exclude($this->ignoreFiles)->ignoreVCS(true)->name($getRegex);

       foreach (iterator_to_array($file_paths, true) as $file_path) {

           $gitIgnoreFiles[] = $file_path->getRelativePathname();
       }
       return $gitIgnoreFiles;

    }

    protected function getExcludedFiles()
    {
        return array_merge($this->getGitIgnoreFiles(), $this->ignoreFiles);
    }
}
