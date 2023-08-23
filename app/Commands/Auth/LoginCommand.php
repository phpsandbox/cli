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
        $status = $this->task('Authenticating', function () use ($auth) {
            try {
                if (! $auth->check()) {
                    if ($this->triggerNewLogin($auth)) {
                        $token = $auth->retrieveToken();

                        return $this->tokenValidation($auth, $token);
                    }

                    return false;
                }

                $this->info('Already authenticated');
            } catch (HttpException $e) {
                $this->error($e->getMessage());

                return false;
            }
        });

        return $status ? Command::SUCCESS : Command::FAILURE;
    }

    protected function tokenValidation(AuthenticationContract  $auth, $token): bool
    {
        try {
            $auth->tokenIsValid($token)
                ? $this->info('Authentication was successful.')
                : $this->error('Token could not be validated.');

            return true;
        } catch (\Exception $e) {

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
            $auth->launchBrowser();
            $this->info("In case you were not redirected, visit {$auth->getTokenUrl()} to access your CLI access code.");
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
