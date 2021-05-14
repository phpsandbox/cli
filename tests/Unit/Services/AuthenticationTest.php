<?php

namespace Tests\Unit\Services;

use App\Services\Authentication;
use App\Services\BrowserService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * @test
     */
    public function retrieveToken(): void
    {
        Storage::makeDirectory('tokens');
        Storage::put('tokens/token', 'token');
        config(['psb.token_storage' => Storage::path('tokens/token')]);

        $auth = new Authentication();
        $this->assertTrue($auth->retrieveToken() == 'token');
    }

    /**
     * @test
     */
    public function testLogout(): void
    {
        Storage::makeDirectory('tokens');
        Storage::put('tokens/token', 'token');
        config(['psb.token_storage' => Storage::path('tokens/token')]);

        $auth = new Authentication();
        $auth->logout();
        Storage::assertMissing('tokens/token');
    }

    /**
     * @test
     */
    public function validTokenIsValid(): void
    {
        /* test valid token */
        $base_url = config('psb.base_url');
        Http::fake([
            sprintf('%s/api/user', $base_url) => Http::response([''], 200),
        ]);
        $auth = new Authentication();
        $this->assertTrue($auth->tokenIsValid('rightToken'));
    }

    /**
     * @test
     */
    public function invalidTokenIsInvalid(): void
    {
        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        $base_url = config('psb.base_url');
        Http::fake([
            sprintf('%s/api/user', $base_url) => Http::response([''], 401),
        ]);
        $auth = new Authentication();
        $this->assertFalse($auth->tokenIsValid('wrongToken'));
    }

    /**
     * @test
     */
    public function storeNewToken(): void
    {
        Storage::makeDirectory('tokens');
        config(['psb.token_storage' => Storage::path('tokens') . '/token']);

        $auth = new Authentication();
        $auth->storeNewToken('token');
        Storage::assertExists('tokens/token');
        $this->assertSame('token', Storage::get('tokens/token'));
    }

    /**
     * @test
     */
    public function launchBrowser(): void
    {
        $this->partialMock(BrowserService::class, function ($mock): void {
            $mock->shouldReceive('open')->once();
        });
        $auth = new Authentication();
        $auth->launchBrowser();
    }
}
