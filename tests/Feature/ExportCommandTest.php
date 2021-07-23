<?php

namespace Tests\Feature;

use App\Contracts\AuthenticationContract;
use App\Contracts\ZipExportContract;
use App\Exceptions\HttpException;
use App\Services\ValidationService;
use Tests\TestCase;

class ExportCommandTest extends TestCase
{
    /**
     * test will export file of unauthenticated user
     *
     * @test
     */
    public function willNotAllowUnauthenticatedUserExportProject(): void
    {
        $this->partialMock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('retrieveToken')->andReturn('token');
        });

        $this->artisan('export')
            ->expectsOutput('Exporting project to phpsandbox : starting')
            ->expectsOutput('Checking for authenticated user: loading...')
            ->expectsConfirmation('Only authenticated users can export to PHPSandbox, do you want to log in now?', 'no')
            ->expectsOutput('Checking for authenticated user: failed')
            ->expectsOutput('Exporting project to phpsandbox : failed')
            ->assertExitCode(0);
    }

    /**
     * test will export file of authenticated user
     *
     * @test
     */
    public function willAllowAuthenticatedUser(): void
    {
        $this->partialMock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('using')->with(getcwd());
            $mock->shouldReceive('check')->andReturn(true);
            $mock->shouldReceive('retrieveToken')->andReturn('token');
        });

        $this->partialMock(ValidationService::class, function ($mock): void {
            $mock->shouldReceive('validate')->twice()->andReturn(true);
        });

        $this->mock(ZipExportContract::class, function ($mock): void {
            $mock->shouldReceive('setWorkingDir')->with(getcwd());
            $mock->shouldReceive('compress')->once()->andReturn('filename.zip');
            $mock->shouldReceive('upload')->once()->andReturn(['notebook' => ['some notebook details'], 'message' => 'some message']);
            $mock->shouldReceive('cleanUp')->once();
            $mock->shouldReceive('openNotebook')->once();
        });

        $this->artisan('export')
            ->expectsOutput('Exporting project to phpsandbox : starting')
            ->expectsOutput('Exporting project to phpsandbox : completed')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function willNotExportIfAuthenticationFails(): void
    {
        $this->mock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('launchBrowser')->once();
            $mock->shouldReceive('fetchCliToken')->once()->andReturnUsing(function (): void {
                throw new HttpException('Invalid token');
            });
            $mock->shouldReceive('getTokenUrl')->andReturn('http://phpsandbox/login/cli');
        });

        $this->artisan('export')
            ->expectsOutput('Exporting project to phpsandbox : starting')
            ->expectsOutput('Checking for authenticated user: loading...')
            ->expectsConfirmation('Only authenticated users can export to PHPSandbox, do you want to log in now?', 'yes')
            ->expectsQuestion('Enter the authentication token copied from the browser', 'some-random-token')
            ->expectsOutput('Exporting project to phpsandbox : failed')
            ->assertExitCode(0);
    }
}
