<?php

namespace Tests\Unit\Services;

use App\Exceptions\HttpException;
use App\Services\AuthenticationService;
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

        $auth = new AuthenticationService();
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

        $auth = new AuthenticationService();
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
        $auth = new AuthenticationService();
        $this->assertTrue($auth->tokenIsValid('rightToken'));
    }

    /**
     * @test
     */
    public function invalidTokenIsInvalid(): void
    {
        $this->expectException(HttpException::class);
        $base_url = config('psb.base_url');
        Http::fake([
            sprintf('%s/api/user', $base_url) => Http::response([''], 401),
        ]);
        $auth = new AuthenticationService();
        $this->assertFalse($auth->tokenIsValid('wrongToken'));
    }

    /**
     * @test
     */
    public function storeNewToken(): void
    {
        Storage::makeDirectory('tokens');
        config(['psb.token_storage' => Storage::path('tokens') . '/token']);

        $auth = new AuthenticationService();
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
        $auth = new AuthenticationService();
        $auth->launchBrowser();
    }
}
