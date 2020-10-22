<?php

namespace App\Services;

use App\Commands\Auth\Auth;
use App\Contracts\AuthenticationContract;
use App\Contracts\BrowserContract;
use App\Http\Client;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;


class Authentication implements AuthenticationContract
{
    protected Client  $client;

    protected $isGuest = false;

    /**
     *  Default uri to generate token;
     */
    protected $tokenUrl;

    /**
     * Default uri to validate token
     */
    protected $validateTokenUrl;

    /**
     * Default token storage store
     *
     * @var String
     */
    protected  $tokenStorage;

    /**
     * Authentication constructor.
     */
    public function  __construct()
    {
        $this
            ->setTokenUrl()
            ->setTokenStorage()
            ->setValidateTokenUrl();
        $this->client = new Client();
    }


    /**
     * Set the default token storage
     */
    protected function setTokenStorage()
    {
        $this->tokenStorage = config('psb.token_storage');
        return $this;
    }

    protected  function  setTokenUrl()
    {
        $this->tokenUrl = config('psb.token_url');
        return $this;
    }

    protected function setValidateTokenUrl()
    {
        $this->validateTokenUrl = config('psb.validate_token_url');
        return $this;
    }

    public function retrieveCliToken($access_token)
    {
        $this->client;
    }

    /**
     * open users browser to retrieve token
     */
    public function  launchBrowser()
    {
        $browser = app()->make(BrowserContract::class);
        $browser->open($this->tokenUrl);
    }

    /**
     * Setup new token
     *
     * @param Auth $command
     */
    public function setUpNewToken(Auth $command)
    {
        $this->launchBrowser();
        $access_token = $command->ask('enter the authentication token generated from the browser');
        $cliToken = $this->fetchCliToken($access_token);
        if(!is_bool($cliToken))
        {
            return   $this->storeNewToken($cliToken);
        }
        return false;



    }

    public function fetchCliToken($access_token)
    {

        return json_decode($this->client->fetchCliToken($access_token),true)['token'];

    }

    /**
     * Store new token to token storage
     *
     * @param $token
     */
    public function storeNewToken($token)
    {   if(!is_dir(dirname($this->tokenStorage)))
        {
            mkdir(dirname($this->tokenStorage));
        }
        File::put($this->tokenStorage,$token);
    }

    public function setGuest()
    {
        $this->isGuest = true;
    }


    /**
     * Checks if a user is authenticated
     * @return bool
     */
    public  function check() : bool
    {
        if (!$this->tokenFileExist()) {
            return false;
        }

        if (!$this->tokenIsValid(File::get($this->tokenStorage))){
            return false;
        }

        return true;
    }

    /**
     * Check if token file exist
     *
     * @return bool
     */
    protected function tokenFileExist() : bool
    {
        try {
            return File::get($this->tokenStorage);
        } catch(FileNotFoundException $e){
            return false;
        }
    }


    /**
     * Check if token is valid
     *
     * @param $token
     * @return bool
     */
    public  function tokenIsValid($token) : bool
    {

        return $this->client->getAuthenticatedUser($token) == 200;

    }

    /**
     * Retrieve token from storage
     *
     * @return string
     */
    public function retrieveToken() : string
    {
        return File::get($this->tokenStorage);
    }
}
