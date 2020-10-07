<?php

namespace App\Services;

use App\Commands\Auth\Auth;
use App\Contracts\AuthenticationContract;
use App\Contracts\BrowserContract;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;


class Authentication implements AuthenticationContract
{

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
    }


    /**
     * Set the default token storage
     */
    protected function setTokenStorage()
    {
        $this->tokenStorage = config('psb.TOKEN_STORAGE');
        return $this;
    }

    protected  function  setTokenUrl()
    {
        $this->tokenUrl = config('psb.TOKEN_URI');
        return $this;
    }

    protected function setValidateTokenUrl()
    {
        $this->validateTokenUrl = config('psb.VALIDATE_TOKEN_URI');
        return $this;
    }

    /**
     * open users browser to retrieve token
     */
    protected function  launchBrowser()
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
        $token = $command->ask('enter the authentication token generated from the browser');
        $this->storeNewToken($token);

    }

    /**
     * Store new token to token storage
     *
     * @param $token
     */
    protected function storeNewToken($token)
    {
        File::put($this->tokenStorage,$token);
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
        sleep(4);
        return true;
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
