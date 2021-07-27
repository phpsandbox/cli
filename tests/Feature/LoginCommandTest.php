<?php

namespace Tests\Feature;

use App\Contracts\AuthenticationContract;
use App\Services\AuthenticationService;
use Tests\TestCase;

class LoginCommandTest extends TestCase
{
    /**
     * @test
     */
    public function freshLoginCommand(): void
    {
        $this->partialMock(AuthenticationService::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('launchBrowser')->once();
            $mock->shouldReceive('fetchCliToken')->once()->andReturn('randomToken');
            $mock->shouldReceive('tokenIsValid')->once()->andReturn(true);
            $mock->shouldReceive('storeNewToken')->once();
        });

        $this->artisan('login')
            ->expectsQuestion('Enter the authentication token copied from the browser', 'randomToken')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function loginWorksIfAccessTokenOptionIsProvidedAndBrowserIsNotOpened(): void
    {
        $this->mock(AuthenticationService::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('fetchCliToken')->once()->andReturn('randomToken');
            $mock->shouldReceive('retrieveToken')->once()->andReturn('rightToken');
            $mock->shouldReceive('tokenIsValid')->once()->andReturn(true);
            $mock->shouldReceive('storeNewToken')->once();
        });

        $this->artisan('login --token=sometoken')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function loginFailsIfTokenIsWrong(): void
    {
        $this->partialMock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('launchBrowser')->once();
            $mock->shouldReceive('fetchCliToken')->once()->andReturn('randomToken');
            $mock->shouldReceive('storeNewToken')->once();
            $mock->shouldReceive('retrieveToken')->once()->andReturn('wrongToken');
            $mock->shouldReceive('tokenIsValid')->with('wrongToken')->once()->andReturn(false);
            $mock->shouldReceive('getTokenUrl')->andReturn('http://phpsandbox/login/cli');
        });

        $this->artisan('login')
            ->expectsQuestion('Enter the authentication token copied from the browser', 'randomToken')
            ->expectsOutput('Token could not be validated.')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function willUseAccessTokenIfProvided(): void
    {
        $this->partialMock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('fetchCliToken')->once()->andReturn('randomToken');
            $mock->shouldReceive('storeNewToken')->once();
            $mock->shouldReceive('retrieveToken')->once()->andReturn('wrongToken');
            $mock->shouldReceive('tokenIsValid')->with('wrongToken')->once()->andReturn(true);
        });

        $this->artisan('login --token=randomTokwn')
            ->expectsOutput('Authentication was successful.')
            ->assertExitCode(0);
    }
}
