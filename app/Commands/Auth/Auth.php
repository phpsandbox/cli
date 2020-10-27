<?php

namespace App\Commands\Auth;

use App\Contracts\AuthenticationContract;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\ConnectionException;
use LaravelZero\Framework\Commands\Command;

class Auth extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */

    protected $signature = 'login  {--access=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Authenticates a User';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(AuthenticationContract  $auth)
    {
        $this->task("Authenticating", function() use($auth) {
            if(!$auth->check())
            {
               $this->triggerNewLogin($auth);
            }

            $token = $auth->retrieveToken();

            $this->tokenValidation($auth,$token);
        });
    }

    protected function tokenValidation(AuthenticationContract  $auth,$token)
    {
        try
        {
            $auth->tokenIsValid($token)
                ? $this->info('Authentication was successful.')
                : $this->error('Token could not be validated.');
        }
        catch(ConnectionException $e)
        {
            return $this->couldNotConnect();
        }
    }

    protected function triggerNewLogin(AuthenticationContract  $auth)
    {

        if ($this->option('access') != null)
        {
            $access_token = $this->option('access');
        }
        else
        {
            $auth->launchBrowser();
            $access_token = $this->ask('enter the authentication token generated from the browser');

        }

        try
       {
           $token = $auth->fetchCliToken($access_token);
           $auth->storeNewToken($token);
       }
       catch (ConnectionException $e)
       {
           $this->couldNotConnect();
           exit;
       }
       catch (\Illuminate\Http\Client\RequestException $e)
       {
           $this->invalidAccessToken();
           exit;
       }

    }

    protected function invalidAccessToken()
    {
        return $this->error('Invalid access token');
    }

    protected function couldNotConnect()
    {
        return $this->error('could not establish a connection. kindly check that your computer is connected to the internet');
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
