<?php

namespace Tests\Feature;

use App\Contracts\AuthenticationContract;
use Tests\TestCase;

class LogoutCommandTest  extends  TestCase
{
    public function test_authenticated_user_is_logged_out()
    {
           $this->partialMock(AuthenticationContract::class,function($mock){
              $mock->shouldReceive('check')->andReturn(true);
              $mock->shouldReceive('logout')->andReturn(true);
           });
            $this->artisan('logout')
                ->expectsOutput('User logged out successfully')
                ->assertExitCode(0);

    }


    public function test_no_logout_if_no_user_was_previously_authenticated()
    {
        $this->partialMock(AuthenticationContract::class,function($mock){
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('logout')->andReturn(true);
        });
        $this->artisan('logout')
            ->expectsOutput('No authenticated user found')
            ->assertExitCode(0);
    }

    public function test_error_occured_during_logout()
    {
        $this->partialMock(AuthenticationContract::class,function($mock){
            $mock->shouldReceive('check')->andReturn(true);
            $mock->shouldReceive('logout')->andReturn(false);
        });
        $this->artisan('logout')
            ->expectsOutput('An error occured')
            ->assertExitCode(0);
    }
}
