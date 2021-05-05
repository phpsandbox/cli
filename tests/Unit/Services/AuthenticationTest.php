<?php


namespace Tests\Unit\Services;


use App\Contracts\AuthenticationContract;
use App\Services\Authentication;
use App\Services\BrowserService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;

class AuthenticationTest  extends TestCase
{

    public function test_retrieve_token()
    {
        Storage::makeDirectory('tokens');
        Storage::put("tokens/token", "token");
        config(['psb.token_storage' => Storage::path("tokens/token")]);

        $auth = new Authentication();
        $this->assertTrue($auth->retrieveToken() == 'token');
    }

    /**
     * @test
     */
    public function test_logout()
    {
        Storage::makeDirectory('tokens');
        Storage::put("tokens/token", "token");
        config(['psb.token_storage' => Storage::path("tokens/token")]);

        $auth = new Authentication();
        $auth->logout();
        Storage::assertMissing("tokens/token");
    }


    public function test_valid_token_is_valid()
    {
        /* test valid token */
        $base_url = config('psb.base_url');
        Http::fake([
            sprintf('%s/api/user',$base_url) => Http::response([''],200)
        ]);
        $auth = new Authentication();
        $this->assertTrue($auth->tokenIsValid('rightToken'));
    }

    public function test_invalid_token_is_invalid()
    {
        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        $base_url = config('psb.base_url');
        Http::fake([
            sprintf('%s/api/user',$base_url)=>Http::response([''],401)
        ]);
        $auth = new Authentication();
        $this->assertFalse($auth->tokenIsValid('wrongToken'));
    }


    public function test_store_new_token()
    {
        Storage::makeDirectory('tokens');
        config(['psb.token_storage' => Storage::path("tokens")."/token"]);

        $auth = new Authentication();
        $auth->storeNewToken('token');
        Storage::assertExists("tokens/token");
        $this->assertSame('token', Storage::get("tokens/token"));
    }

    public function test_launch_browser()
    {
        $this->partialMock(BrowserService::class,function($mock){
           $mock->shouldReceive('open')->once();
        });
        $auth = new Authentication();
        $auth->launchBrowser();
    }
}
