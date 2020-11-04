<?php


namespace Tests\Feature;


use App\Contracts\AuthenticationContract;
use App\Services\Authentication;
use Mockery;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;

class LoginCommandTest  extends TestCase
{


    public function test_fresh_login_command()
    {
        $this->partialMock(Authentication::class, function ($mock) {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('launchBrowser')->once();
            $mock->shouldReceive('fetchCliToken')->once()->andReturn('randomToken');
            $mock->shouldReceive('tokenIsValid')->once()->andReturn(true);
            $mock->shouldReceive('storeNewToken')->once();
        });

        $this->artisan('login')
            ->expectsQuestion('Enter the authentication token generated from the browser','randomToken')
            ->assertExitCode(0);
    }

    public function test_login_fails_if_token_is_wrong()
    {
        $this->partialMock(AuthenticationContract::class, function ($mock) {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('launchBrowser')->once();
            $mock->shouldReceive('fetchCliToken')->once()->andReturn('randomToken');
            $mock->shouldReceive('storeNewToken')->once();
            $mock->shouldReceive('retrieveToken')->once()->andReturn('wrongToken');
            $mock->shouldReceive('tokenIsValid')->with('wrongToken')->once()->andReturn(false);

        });

        $this->artisan('login')
            ->expectsQuestion('Enter the authentication token generated from the browser','randomToken')
            ->expectsOutput('Token could not be validated.')
            ->assertExitCode(0);
    }

    public function test_will_use_access_token_if_provided()
    {
        $this->partialMock(AuthenticationContract::class,function ($mock){
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
