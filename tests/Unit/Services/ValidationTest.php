<?php


use App\Services\Validation;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;

/**
 * Class ValidationTest
 */
class ValidationTest extends TestCase
{


    /**
     * @var Validation
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new Validation();
        $fileStorage = env('FILES_STORAGE');

    }

    /**
     * @test
     */
    public function test_composer_is_valid_rule()
    {
        $invalid_structure = [
            'composer.json'=>''
        ];
        $valid_structure = [
            'composer.json'=>json_encode([''])
        ];

        $this->assertFalse($this->validator->validate(
            vfsStream::setup('root',null, $invalid_structure)->url(),[
            'composerIsValid'
        ]));

        $this->assertTrue(count($this->validator->errors()) == 1);

        $this->assertTrue($this->validator->validate(
            vfsStream::setup('root',null, $valid_structure)->url(),[
            'composerIsValid'
        ]));

        $this->assertTrue(count($this->validator->errors()) == 1);

    }


    /**
     * @test
     */
    public function test_file_size_rule()
    {
        $valid_size = env('MAX_FILE_SIZE');
        $invalid_size = $valid_size * 2;
        $root      = vfsStream::setup();
        $smallFile = vfsStream::newFile('small.zip')
            ->withContent(LargeFileContent::withKilobytes($valid_size))
            ->at($root);

       $largeFile = vfsStream::newFile('large.zip')
            ->withContent(LargeFileContent::withKilobytes($invalid_size))
            ->at($root);



        $this->assertTrue($this->validator->validate($root,[
            "size,".$smallFile->url()
        ]));
        $this->assertFalse($this->validator->validate($root,[
            "size,".$largeFile->url()
        ]));

    }


    /**
     * @test
     */
    public function test_has_composer_json_rule()
    {
        $invalid_structure = [
            'app'=>[
                'sample.php' => 'some sample text'
            ]
        ];

        $valid_structure = [
            'composer.json'=>''
        ];

        $this->assertFalse($this->validator->validate(
            vfsStream::setup('root',null, $invalid_structure)->url(),[
                'hasComposer'
            ])
        );
        $this->assertTrue(count($this->validator->errors()) === 1);

        $this->assertTrue($this->validator->validate(
            vfsStream::setup('root',null, $valid_structure)->url(), [
                'hasComposer'
            ])
        );

        $this->assertTrue(count($this->validator->errors()) <= 1);

    }


    public function tearDown(): void
    {
        $this->validator = null;
    }
}
