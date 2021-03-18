<?php


namespace Tests\Unit\Services;


use App\Contracts\BrowserContract;
use App\Services\BrowserService;
use Tests\TestCase;

class BrowserTest extends TestCase
{

    public function test_will_launch_browser()
    {
        $this->partialMock(BrowserService::class, function($mock) {
            $mock->shouldReceive('getSystemCommand')
                ->andReturn('open');
           $mock->shouldReceive('runCommand')
               ->with('open http://phpsandbox.test')
               ->andReturn(true);
        });

        $browser = app(BrowserContract::class);
        $browser->open("http://phpsandbox.test");
    }

}
