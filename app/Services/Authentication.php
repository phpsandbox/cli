<?php

namespace App\Services;

use App\Contracts\AuthenticationContract;
use App\Contracts\BrowserContract;
use App\Http\Client;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;

/**
 * Class Authentication
 */
class Authentication implements AuthenticationContract
{
    /**
     * @var Client
     *
     * instance of the client class
     */
    protected Client  $client;

    /**
     *  Default uri to generate token;
     */
    protected string $tokenUrl;

    /**
     * Default uri to validate token
     */
    protected string $validateTokenUrl;

    /**
     * Default token storage store
     */
    protected $tokenStorage;

    public function __construct()
    {
        $this->setTokenUrl()
            ->setTokenStorage()
            ->setValidateTokenUrl();

        $this->client = new Client();
    }

    protected function setTokenStorage(): Authentication
    {
        $this->tokenStorage = config('psb.token_storage');

        return $this;
    }

    protected function setTokenUrl(): Authentication
    {
        $this->tokenUrl = sprintf('%s/login/cli', config('psb.base_url'));

        return $this;
    }

    protected function setValidateTokenUrl(): Authentication
    {
        $this->validateTokenUrl = sprintf('%s/api/cli/login', config('psb.base_url'));

        return $this;
    }

    /**
     * open users browser to retrieve token
     */
    public function launchBrowser(): void
    {
        $browser = app()->make(BrowserContract::class);
        $browser->open($this->tokenUrl);
    }

    public function fetchCliToken(string $access_token): string
    {
        return $this->client->fetchCliToken($access_token);
    }

    public function storeNewToken(string $token): void
    {
        if (! is_dir(dirname($this->tokenStorage))) {
            mkdir(dirname($this->tokenStorage));
        }

        File::put($this->tokenStorage, $token);
    }

    public function check(): bool
    {
        if (! $this->tokenFileExist()) {
            return false;
        }
        try {
            return $this->tokenIsValid(File::get($this->tokenStorage));
        } catch (RequestException $e) {
            $this->deleteTokenFile();

            return false;
        }
    }

    protected function tokenFileExist(): bool
    {
        return File::isFile($this->tokenStorage);
    }

    public function tokenIsValid(string $token): bool
    {
        return $this->client->getAuthenticatedUser($token);
    }

    public function retrieveToken(): string
    {
        try {
            return File::get($this->tokenStorage);
        } catch (FileNotFoundException $e) {
            return '';
        }
    }

    public function logout(): bool
    {
        return $this->deleteTokenFile();
    }

    protected function deleteTokenFile(): bool
    {
        return unlink($this->tokenStorage);
    }
}
