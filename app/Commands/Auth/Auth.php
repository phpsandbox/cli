<?php

namespace App\Commands\Auth;

use App\Contracts\AuthenticationContract;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use LaravelZero\Framework\Commands\Command;

class Auth extends Command
{
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
        $this->task("Authenticating", function() use($auth) {
            if(! $auth->check() || $this->option('access') != null) {
               $this->triggerNewLogin($auth);
            }

            $token = $auth->retrieveToken();

            $this->tokenValidation($auth,$token);
        });
    }

    protected function tokenValidation(AuthenticationContract  $auth,$token)
    {
        try {
            $auth->tokenIsValid($token)
                ? $this->info('Authentication was successful.')
                : $this->error('Token could not be validated.');
        } catch(ConnectionException $e) {
            return $this->couldNotConnect();
        }catch (\Illuminate\Http\Client\RequestException $e){
            return $this->invalidAccessToken();
        }
    }

    protected function triggerNewLogin(AuthenticationContract  $auth)
    {
        if ($this->option('access') != null) {
            $access_token = $this->option('access');
        } else {
            $auth->launchBrowser();
            $access_token = $this->ask('enter the authentication token generated from the browser');
        }

        try {
            $token = $auth->fetchCliToken($access_token);
            $auth->storeNewToken($token);
        } catch (ConnectionException $e) {
            $this->couldNotConnect();
            exit;
        } catch (RequestException $e) {
            if ($e->getCode() === 422) {
                $this->invalidAccessToken();
            } else {
                $this->errorOccured();
            }
            exit;
        }
    }

    protected function invalidAccessToken()
    {
        $this->error('Invalid access token.');
    }

    protected function errorOccured()
    {
        $this->error('An error occured!');
    }

    protected function couldNotConnect()
    {
        $this->error('Could not establish a connection. Kindly check that your computer is connected to the internet.');
    }


    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
