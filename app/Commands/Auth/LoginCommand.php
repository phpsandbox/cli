<?php

namespace App\Commands\Auth;

use App\Contracts\AuthenticationContract;
use App\Exceptions\HttpException;
use App\Traits\FormatHttpErrorResponse;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
{
    use FormatHttpErrorResponse;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'login  {--token=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Authenticates a User';

    /**
     * Execute the console command.
     *
     * @param AuthenticationContract $auth
     * @return mixed
     */
    public function handle(AuthenticationContract  $auth)
    {
        $this->task('Authenticating', function () use ($auth) {
            if (! $auth->check()) {
                if ($this->triggerNewLogin($auth)) {
                    $token = $auth->retrieveToken();

                    return $this->tokenValidation($auth, $token);
                }

                return false;
            }

            $this->info('Already authenticated');

            return true;
        });
    }

    protected function tokenValidation(AuthenticationContract  $auth, $token): bool
    {
        try {
            $auth->tokenIsValid($token)
                ? $this->info('Authentication was successful.')
                : $this->error('Token could not be validated.');

            return true;
        } catch (HttpException $e) {
            $this->error($e->getMessage());

            return false;
        }
    }

    protected function triggerNewLogin(AuthenticationContract  $auth): bool
    {
        if ($this->option('token') != null) {
            $access_token = $this->option('token');
        } else {
            $this->newLine();
            $this->info('You would be redirected to your browser to obtain your access token.');
            tap($auth->launchBrowser(), function ($url): void {
                $this->info("In case you were not redirected, visit $url to access your CLI access code.");
            });
            $access_token = $this->ask('Enter the authentication token copied from the browser');
        }

        try {
            $token = $auth->fetchCliToken($access_token);
            $auth->storeNewToken($token);

            return true;
        } catch (HttpException $e) {
            $this->error($e->getMessage());

            return false;
        }
    }
}
