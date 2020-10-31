<?php


namespace Tests\Unit\Services;


use App\Contracts\ZipExportContract;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;

class ZipExportTest  extends TestCase
{
    public function setUp(): void
    {
        $this->zip = $this->createApplication()->make(ZipExportContract::class);
    }

    public function test_zip_would_clean_up_test_directories_after()
    {
        $structure = [
            'files'=>[
                'file.zip'=>''
            ]
        ];
        $root = vfsStream::setup('root', null, $structure);
        config(['psb.files_storage'=>$root->url().'/files']);
        $this->zip->cleanUp();
        $this->assertFalse($root->hasChild('files/files.zip'));
    }

    public function test_zip_creates_file_after_compression()
    {

    }
}
