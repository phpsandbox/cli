<?php

namespace Tests\Unit\Services;

use App\Services\ImportNotebookService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpZip\ZipFile;
use Tests\TestCase;

class ImportNotebookServiceTest extends TestCase
{
    /**
     * @test
     */
    public function willSetStorageDirectory(): void
    {
        $importService = new ImportNotebookService('unique-id');
        $importService->setStorageDirectory('.');
        $this->assertSame($importService->getStorageDirectory(), getcwd() . '/unique-id');

        $importService->setStorageDirectory();
        $this->assertSame($importService->getStorageDirectory(), getcwd() . '/unique-id');

        Storage::makeDirectory('sample');
        $importService->setStorageDirectory(Storage::path('sample'));
        $this->assertSame($importService->getStorageDirectory(), Storage::path('sample') . '/unique-id');
    }

    /**
     * @test
     */
    public function willDownloadNotebookZip(): void
    {
        /**
         * Create a dummy zip file
         */
        Storage::fake();
        Storage::makeDirectory('sampleFiles');
        Storage::makeDirectory('extractToDirectory');
        Storage::makeDirectory('sampleZipFiles');
        Storage::put('sampleFiles/file1.php', 'hello world');

        (new ZipFile())->addDir(Storage::path('sampleFiles'))
            ->saveAsFile(Storage::path('sampleZipFiles') . '/sample.zip');

        /**
         * Setup mocks
         */
        $notebookUniqueId = 'unique-id';
        config(['psb.files_storage' => Storage::path('extractToDirectory')]);

        Http::fake([
            '*' => Http::response(file_get_contents(Storage::path('sampleZipFiles/sample.zip')), 200),
        ]);

        $importService = new ImportNotebookService('unique-id');
        $importService->downloadNotebookZip(fn () => null);

        /**
         * Test for side effects
         */
        Storage::assertExists('extractToDirectory/unique-id.zip');
    }

    /**
     * @test
     */
    public function extractFiles(): void
    {
        /**
         * Create a dummy zip file
         */
        Storage::fake();
        Storage::makeDirectory('sampleFiles');
        Storage::makeDirectory('extractToDirectory');
        Storage::makeDirectory('sampleZipFiles');
        Storage::put('sampleFiles/file1.php', 'hello world');

        (new ZipFile())->addDir(Storage::path('sampleFiles'))
            ->saveAsFile(Storage::path('sampleZipFiles') . '/unique-id.zip');

        /**
         * Set up mocks
         */
        config(['psb.files_storage' => Storage::path('sampleZipFiles')]);

        /**
         * Run service
         */
        $importService = new ImportNotebookService('unique-id');
        $importService->setStorageDirectory(Storage::path('extractTodirectory'));
        $importService->extractFiles();

        /**
         * Test side effects
         */
        $this->assertDirectoryExists(Storage::path('extractToDirectory/unique-id'));
        Storage::assertExists('extractToDirectory/unique-id/file1.php');
        $this->assertSame('hello world', Storage::get('extractToDirectory/unique-id/file1.php'));
    }
}
