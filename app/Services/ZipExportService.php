<?php

namespace App\Services;

use App\Contracts\BrowserContract;
use App\Contracts\ZipExportContract;
use App\Http\Client;
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
        $files = glob(config('psb.files_storage').DIRECTORY_SEPARATOR."*");

        foreach($files as $file)
        {
            unlink($file);
        }
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
          mkdir($this->fileStoragePath);
      }
      return implode(DIRECTORY_SEPARATOR,[$this->fileStoragePath,$path]);
    }

    public function upload($filepath, $token = '')
    {
        return $this->client->uploadCompressedFile($filepath, $token);
    }

    protected function getNotebookUrl(array $details, $token)
    {
        //i am hard coding this here for now, would change it once i see bosun's code and how to set a common
        //base url for all urls
        return $token == ''
            ? sprintf('https://internal.phpsandbox.io/n/%s?accessToken=%s', $details['unique_id'], $details['settings']['accessToken'])
            : sprintf('https://internal.phpsandbox.io/n/%s', $details['unique_id']);
    }

    public function openNotebook(array $details, $token)
    {
        $browser = app()->make(BrowserContract::class);
        $browser->open($this->getNotebookUrl($details, $token));
    }
}
