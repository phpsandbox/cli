<?php

namespace App\Services;

use App\Contracts\ZipExportContract;
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
                                    'storage',
                              ];
    /**
     * The external zip object
     *
     * @var ZipFile
     */
    protected  $zipper;

    /**
     * ZipExportService constructor.
     */
    public function __construct()
    {
        $this->zipper = new ZipFile();
    }

    /**
     *  Handle the export process
     */
    public function export()
    {
        $this->createZip();
    }


    /**
     *
     */
    protected function createZip()
    {
        try {
            $directoryIterator = new RecursiveDirectoryIterator($this->getZipPath());

            $ignoreIterator = new IgnoreFilesRecursiveFilterIterator(
                $directoryIterator,
                $this->ignoreFiles
            );
            $this->zipper->addFilesFromIterator($ignoreIterator)
                        ->saveAsFile(sha1(microtime()).".zip");
        } catch (ZipException $e){
            //there was a problem with the compression
        }

    }

    /**
     * Get the path to the directory to be compressed
     *
     * @return string
     */
    public function getZipPath()
    {
        return getcwd().DIRECTORY_SEPARATOR.'sample';
    }

    /**
     * Get the path to store the compressed file
     *
     * @return false|string
     */
    public function getStoragePath()
    {
        return getcwd();
    }
}
