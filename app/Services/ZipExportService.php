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
                                    'vendor/',
                                    'node_modules/',
                                    '.git',

                                    /**
                                     * We should add this if app is a laravel app
                                     * We will need some kind of notebook type detector here
                                     */
//                                    'storage',
                              ];
    /**
     * The external zip object
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
        $ignore = ['vendor','.','.git','..','node_modules'];
        $files = scandir($path);
        foreach($files as $t) {
            if(in_array($t, $ignore)) continue;
            if (is_dir(rtrim($path, '/') . '/' . $t)) {
                $size += $this->countFiles(rtrim($path, '/') . '/' . $t);
            } else {
                $size++;
            }
        }
        return $size;
    }

    /**
     *  Handle the export process
     */
    public function compress()
    {
        return $this->createZip();
    }


    /**
     *
     */
    protected function createZip()
    {
            $directoryIterator = new RecursiveDirectoryIterator($this->getZipPath());

            $ignoreIterator = new IgnoreFilesRecursiveFilterIterator(
                $directoryIterator,
                $this->ignoreFiles
            );
            $compressed_file_name = sha1(microtime()).".zip";
            $full_file_path = $this->getStoragePath($compressed_file_name);
            $this->zipper->addFilesFromIterator($ignoreIterator)
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
    protected function getStoragePath($path = '')
    {
      if (!is_dir($this->fileStoragePath))
      {
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

    public function openNotebook(array $details, $token)
    {
        $browser = app()->make(BrowserContract::class);
        $browser->open($this->getNotebookUrl($details, $token));
    }
}
