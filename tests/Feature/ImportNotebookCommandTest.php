<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpZip\ZipFile;
use Tests\TestCase;

class ImportNotebookCommandTest extends TestCase
{
    /**
     * @test
     */
    public function willImportNotebookFromPHPSandbox(): void
    {
        /**
         * Create a dummy zip file
         */
        Storage::fake();
        Storage::makeDirectory('sampleFiles');
        Storage::makeDirectory('extractToDirectory');
        Storage::makeDirectory('sampleZipFiles');
        Storage::put('sampleFiles/file1.php', 'hello world');
        Storage::put('sampleFiles/composer.json', '{}');

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

        /**
         * Call the artisan command
         */
        $this->artisan("import $notebookUniqueId --path=" . Storage::path('extractToDirectory'))
            ->expectsOutput('Importing notebook from PHPsandbox : starting')
            ->expectsOutput('Verify extraction directory: ✔')
            ->expectsOutput('Downloading notebook: loading...')
            ->expectsOutput('Extracting notebook: ✔')
            ->expectsOutput('Importing notebook from PHPsandbox : completed');

        /**
         * Test for side effects
         */
        Storage::assertMissing('extractToDirectory/unique-id.zip');
        $this->assertDirectoryExists(Storage::path('extractToDirectory/unique-id'));
        Storage::assertExists('extractToDirectory/unique-id/file1.php');
        $this->assertSame('hello world', Storage::get('extractToDirectory/unique-id/file1.php'));
        Storage::assertExists('extractToDirectory/unique-id/composer.json');
        $this->assertDirectoryExists(Storage::path('extractToDirectory/unique-id/vendor'));
    }

    /**
     * @test
     */
    public function willNotExportIfTheStorageDirectoryProvidedIsNotValid(): void
    {
        $notebookUniqueId = 'unique-id';
        $this->artisan("import $notebookUniqueId -p someFakeDirectory")
            ->expectsOutput('Verify extraction directory: loading...')
            ->expectsOutput('The directory does not exist')
            ->expectsOutput('Verify extraction directory: failed')
            ->expectsOutput('Importing notebook from PHPsandbox : failed');
    }
}
