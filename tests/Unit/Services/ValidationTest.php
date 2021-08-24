<?php

use App\Services\ValidationService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class ValidationTest
 */
class ValidationTest extends TestCase
{
    /**
     * @var ValidationService
     */
    private ValidationService $validator;

    protected function setUp(): void
    {
        $this->validator = new ValidationService();
        $app = $this->createApplication();
        Storage::fake();
    }

    /**
     * @test
     */
    public function testComposerIsValidRule(): void
    {
        Storage::makeDirectory('valid_project');
        Storage::put('valid_project/composer.json', '{}');

        Storage::makeDirectory('invalid_project');
        Storage::put('invalid_project/composer.json', ' ');

        $this->assertTrue($this->validator->validate(
            Storage::path('valid_project'),
            [
                'composerIsValid',
            ]
        ));

        $this->assertTrue(count($this->validator->errors()) == 0);

        $this->assertFalse($this->validator->validate(
            Storage::path('invalid_project'),
            [
                'composerIsValid',
            ]
        ));

        $this->assertTrue(count($this->validator->errors()) == 1);
    }

    /**
     * @test
     */
    public function fileSizeRule(): void
    {
        /* test will fail for large files. We simulate this by setting max_file_size to negative*/
        config(['psb.max_file_size' => -1]);

        Storage::makeDirectory('project');
        Storage::makeDirectory('project:withcolon');

        Storage::put('project/index.zip', "<?php echo 'hello' ?>");

        $this->assertFalse($this->validator->validate(Storage::path('project'), [
            'size:' . Storage::path('project/index.zip'),
        ]));

        /* test for small files */

        config(['psb.max_file_size' => 10]);

        Storage::put('project/hello.zip', "<?php echo 'hello' ?>");

        $this->assertTrue($this->validator->validate(Storage::path('project'), [
            'size:' . Storage::path('project/hello.zip'),
        ]));

        /** test for file path containing colon */
        Storage::put('project:withcolon/hello.zip', "<?php echo 'hello' ?>");

        $this->assertTrue($this->validator->validate(Storage::path('project'), [
            'size:' . Storage::path('project:withcolon/hello.zip'),
        ]));
    }

    /**
     * @test
     */
    public function hasComposerJsonRule(): void
    {
        Storage::makeDirectory('valid_project');
        Storage::put('valid_project/composer.json', '{}');

        Storage::makeDirectory('invalid_project');

        $this->assertTrue(
            $this->validator->validate(
                Storage::path('valid_project'),
                [
                    'hasComposer',
                ]
            )
        );
        $this->assertCount(0, $this->validator->errors());

        $this->assertFalse(
            $this->validator->validate(
                Storage::path('invalid_project'),
                [
                    'hasComposer',
                ]
            )
        );

        $this->assertTrue(count($this->validator->errors()) > 0);
    }

    protected function tearDown(): void
    {
        $this->validator = new ValidationService();
        $app = null;
    }
}
