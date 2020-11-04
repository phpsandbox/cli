<?php

namespace App\Commands\Auth;

use App\Contracts\AuthenticationContract;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
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
            if(! $auth->check()) {
               $this->triggerNewLogin($auth);
                $token = $auth->retrieveToken();
                return $this->tokenValidation($auth,$token);
            }
            $this->info('Already authenticated');
            return true;
        });
    }

    protected function tokenValidation(AuthenticationContract  $auth,$token)
    {
        try {
            $auth->tokenIsValid($token)
                ? $this->info('Authentication was successful.')
                : $this->error('Token could not be validated.');
            return true;
        } catch(ConnectionException $e) {
            $this->couldNotConnect();
        } catch (RequestException $e){
           if($e->getCode() == 422){
               $this->invalidAccessToken();
        } else{

            $this->error($e->getMessage());
            }
        }
        return false;

    }

    protected function triggerNewLogin(AuthenticationContract  $auth)
    {
        if ($this->option('token') != null) {
            $access_token = $this->option('token');
        } else {
            $auth->launchBrowser();
            $access_token = $this->ask('Enter the authentication token generated from the browser');
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