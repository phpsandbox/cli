<?php

namespace Tests\Feature;

use App\Contracts\AuthenticationContract;
use Tests\TestCase;

class LogoutCommandTest extends TestCase
{
    /**
     * @test
     */
    public function authenticatedUserIsLoggedOut(): void
    {
        $this->partialMock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(true);
            $mock->shouldReceive('logout')->andReturn(true);
        });
        $this->artisan('logout')
            ->expectsOutput('User logged out successfully')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function noLogoutIfNoUserWasPreviouslyAuthenticated(): void
    {
        $this->partialMock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('logout')->andReturn(true);
        });
        $this->artisan('logout')
            ->expectsOutput('No authenticated user found')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function errorOccuredDuringLogout(): void
    {
        $this->partialMock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(true);
            $mock->shouldReceive('logout')->andReturn(false);
        });
        $this->artisan('logout')
            ->expectsOutput('An error occurred')
            ->assertExitCode(0);
    }
}
