<?php

namespace Tests\Unit\Services;

use App\Contracts\ZipExportContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use org\bovigo\vfs\vfsStream;
use PhpZip\ZipFile;
use Tests\TestCase;

class ZipExportTest  extends TestCase
{
    public function setUp(): void
    {
        $this->zip = $this->createApplication()->make(ZipExportContract::class);
    }

    public function test_zip_would_clean_up_created_zip_files_after_upload()
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
        //create a folder, add some files. compress it.
        @mkdir(base_path("tests/sample"));

        for($i=0; $i<= 5; $i++)
        {
            fopen(base_path("tests/sample/file{$i}.php"), 'w');
        }
        $this->zip->setWorkingDir(base_path("tests/sample"));

        $filename = $this->zip->compress();

        $this->assertTrue(File::exists($filename));

    }

    public function test_zip_will_ignore_standard_files_while_compressing()
    {
        @mkdir(base_path("tests/sample"));

        for ($i=0; $i<= 5; $i++) {
            fopen(base_path("tests/sample/file{$i}.php"), 'w');
        }
        //create a vendors, node_modules
        @mkdir(base_path("tests/sample/vendor"));
        @mkdir(base_path("tests/sample/node_modules"));
        @mkdir(base_path("tests/sample/.git"));

        $this->zip->setWorkingDir(base_path("tests/sample"));
        $filename = $this->zip->compress();
        $zipper = new ZipFile();
        $zipFiles = $zipper->openFile($filename);

        $this->assertFalse(Arr::exists($zipFiles, "vendor"));
        $this->assertFalse(Arr::exists($zipFiles, "node_modules"));
        $this->assertFalse(Arr::exists($zipFiles, ".git"));

    }

    public function test_will_ignore_git_ignore_files_for_git_projects()
    {
        @mkdir(base_path("tests/sample"));

        for ($i=0; $i<= 5; $i++) {
            fopen(base_path("tests/sample/file{$i}.php"), 'w');
        }
        //create sample files
        @mkdir(base_path("tests/sample/.git"));
        fopen(base_path("tests/sample/.gitignore"), 'w');

        //add some content to gitifnore
        $gitignoreContent = [".git","file1.php", "file2.php"];
        file_put_contents(base_path("tests/sample/.gitignore"), implode("\n", $gitignoreContent));

       $filename =  $this->zip->setWorkingDir(base_path("tests/sample"))->compress();

        $zipper = new ZipFile();
        $zipFiles = $zipper->openFile($filename);

        foreach ($gitignoreContent as $ignore) {
            $this->assertFalse(Arr::exists($zipFiles, $ignore));
        }

    }
    public function tearDown(): void
    {
        system("rm -rf ".escapeshellarg(base_path("tests/sample")));
    }
}
