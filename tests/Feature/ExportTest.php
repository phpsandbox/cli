<?php

namespace Tests\Feature;

use App\Contracts\AuthenticationContract;
use App\Contracts\ZipExportContract;
use App\Services\Validation;
use Tests\TestCase;

class ExportTest extends TestCase
{
    /**
     * test will export file of unauthenticated user
     *
     * @test
     */
    public function willAllowUnauthenticatedUserExportProject(): void
    {
        $this->partialMock(AuthenticationContract::class, function ($mock): void {
            $mock->shouldReceive('check')->andReturn(false);
            $mock->shouldReceive('retrieveToken')->andReturn('token');
        });
        $this->partialMock(Validation::class, function ($mock): void {
            $mock->shouldReceive('validate')->twice()->andReturn(true);
        });
        $this->mock(ZipExportContract::class, function ($mock): void {
            $mock->shouldReceive('setWorkingDir')->with(getcwd());
            $mock->shouldReceive('compress')->once()->andReturn('filename.zip');
            $mock->shouldReceive('upload')->once()->andReturn(['notebook_details']);
            $mock->shouldReceive('cleanUp')->once();
            $mock->shouldReceive('openNotebook')->once();
        });

        $this->artisan('export')
            ->expectsQuestion('You are not authenticated, do you want to continue as guest?', 'yes')
            ->expectsOutput('Authenticated as guest')
            ->expectsOutput('Exporting project to phpsandbox : starting')
            ->expectsOutput('Exporting project to phpsandbox : completed')
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
        $this->partialMock(Validation::class, function ($mock): void {
            $mock->shouldReceive('validate')->twice()->andReturn(true);
        });
        $this->mock(ZipExportContract::class, function ($mock): void {
            $mock->shouldReceive('setWorkingDir')->with(getcwd());
            $mock->shouldReceive('compress')->once()->andReturn('filename.zip');
            $mock->shouldReceive('upload')->once()->andReturn(['notebook_details']);
            $mock->shouldReceive('cleanUp')->once();
            $mock->shouldReceive('openNotebook')->once();
        });

        $this->artisan('export')
            ->expectsOutput('Exporting project to phpsandbox : starting')
            ->expectsOutput('Exporting project to phpsandbox : completed')
            ->assertExitCode(0);
    }
}
