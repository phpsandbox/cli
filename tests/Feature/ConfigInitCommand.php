<?php


namespace Tests\Feature;


use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConfigInitCommand extends TestCase
{
    /**
     * @dataProvider availableTemplates
     */
    public function test_will_create_config_file($template)
    {
        Storage::makeDirectory('sample');
        config(['psb.config_file_storage' => Storage::path('sample')]);

        $this->artisan("config:init")
            ->expectsQuestion("Which notebook template will the project be identified as?", $template);

        Storage::assertExists('sample/phpsandbox.json');
        $this->assertSame(Storage::get('sample/phpsandbox.json'), json_encode(['template' => $template]));
    }


    public function availableTemplates(): array
    {
        return [
            ["laravel"],
            ["symfony"],
            ["slim"],
            ["standard"],
            ["react-php-http"]
        ];
    }

    public function test_will_detect_invalid_config_file()
    {
        Storage::makeDirectory('sample');
        Storage::put('sample/phpsandbox.json', '{');
        config(['psb.config_file_storage' => Storage::path('sample')]);

        $this->artisan("config:init")
            ->expectsQuestion("Which notebook template will the project be identified as?", 'laravel')
            ->expectsOutput("Invalid configuration file");

    }
}
