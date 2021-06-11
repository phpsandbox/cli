<?php

namespace Tests\Unit\Services;

use App\Services\ZipExportService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpZip\ZipFile;
use Tests\TestCase;

class ZipExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->zip = new ZipExportService();
    }

    /**
     * @test
     */
    public function zipCleanup(): void
    {
        Storage::makeDirectory('projects');
        Storage::put('projects/index.zip', 'token');
        config(['psb.files_storage' => Storage::path('projects')]);

        $this->zip->cleanUp();
        Storage::assertMissing('index.zip');
    }

    /**
     * @test
     */
    public function zipCreatesFileAfterCompression(): void
    {
        //create a folder, add some files. compress it.
        Storage::makeDirectory('project');
        Storage::makeDirectory('storage');

        config(['psb.files_storage' => Storage::path('storage')]);

        for ($i = 0; $i <= 5; $i++) {
            Storage::put('project/' . $i . '.php', '<?php ?>');
        }

        $this->zip->setWorkingDir(Storage::path('project'));

        $filename = $this->zip->compress();

        $this->assertTrue(File::exists($filename));
    }

    /**
     * @test
     */
    public function zipWillIgnoreStandardFilesWhileCompressing(): void
    {
        Storage::makeDirectory('project');

        for ($i = 0; $i <= 5; $i++) {
            Storage::put('project/' . $i . '.php', '<?php ?>');
        }
        //create a vendors, node_modules
        Storage::makeDirectory('project/vendor');
        Storage::makeDirectory('project/node_modules');
        Storage::makeDirectory('project/.git');

        $this->zip->setWorkingDir(Storage::path('project'));
        $filename = $this->zip->compress();
        $zipper = new ZipFile();
        $zipFiles = $zipper->openFile($filename);

        $this->assertFalse(Arr::exists($zipFiles, 'vendor'));
        $this->assertFalse(Arr::exists($zipFiles, 'node_modules'));
        $this->assertFalse(Arr::exists($zipFiles, '.git'));
    }

    /**
     * @test
     */
    public function willIgnoreGitIgnoreFilesForGitProjects(): void
    {
        Storage::makeDirectory('project');

        for ($i = 0; $i <= 5; $i++) {
            Storage::put('project/file' . $i . '.php', '<?php ?>');
        }
        //create a vendors, node_modules
        Storage::makeDirectory('project/vendor');
        Storage::makeDirectory('project/node_modules');
        Storage::makeDirectory('project/.git');

        $gitignoreContent = ['.git','file1.php', 'file2.php', 'vendor', 'node_modules'];
        Storage::put('project/.gitignore', implode("\n", $gitignoreContent));

        $filename = $this->zip->setWorkingDir(Storage::path('project'))->compress();

        $zipper = new ZipFile();
        $zipFiles = $zipper->openFile($filename);

        foreach ($gitignoreContent as $ignore) {
            $this->assertFalse(Arr::exists($zipFiles, $ignore));
        }
    }

    protected function tearDown(): void
    {
        $this->zip = new ZipExportService();
    }
}
