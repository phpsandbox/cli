<?php

namespace App\Http;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Client
{
    protected $headers = [];

    /**
     *  Default uri to generate token;
     */
    protected $redirectToBrowserUrl;

    /**
     * Default uri to validate token
     */
    protected $fetchCliTokenUrl;

    protected $fetchAuthUserUrl;

    /**
     * @var Repository|Application|mixed
     */
    private $fileUploadUrl;

    /**
     * @var PendingRequest
     */
    private $httpClient;

    public function __construct()
    {
        $this
            ->setRedirectToBrowserUrl()
            ->setFetchCliTokenUrl()
            ->setFileUploadUrl()
            ->setFetchAuthUserUrl();

        $this->buildHttpClient();
    }

    protected function setFileUploadUrl(): static
    {
        $this->fileUploadUrl = '/cli/import';

        return $this;
    }

    protected function setRedirectToBrowserUrl(): static
    {
        $this->redirectToBrowserUrl = sprintf('%s/login/cli', config('psb.base_url'));

        return $this;
    }

    protected function setFetchCliTokenUrl(): static
    {
        $this->fetchCliTokenUrl = 'cli/login';

        return $this;
    }

    public function setFetchAuthUserUrl(): void
    {
        $this->fetchAuthUserUrl = '/user';
    }

    public function fetchCliToken($access_token): string
    {
        $response = $this->withMainHeaders()->getClient()->post($this->fetchCliTokenUrl, ['code' => $access_token]);

        return $response->throw()->json()['token'];
    }

    public function getAuthenticatedUser($token): bool
    {
        $response = $this->withMainHeaders()->authenticateAs($token)->getClient()->get($this->fetchAuthUserUrl);

        return $response->throw()->successful();
    }

    public function downloadNotebook(string $uniqueId, Closure $progressCallback)
    {
        return $this->withMainHeaders()->getClient()->withOptions([
            'sink' => config('psb.files_storage') . "/$uniqueId.zip",
            'progress' => $progressCallback,
        ])->get("/notebook/download/$uniqueId")->throw()->body();
    }

    public function uploadCompressedFile($file_path, $token)
    {
        $client = $token != ''
            ? $this->authenticateAs($token)->getClient()
            : $this->getClient();

        $response = $client->attach('archive', fopen($file_path, 'r'))
            ->post($this->fileUploadUrl);

        return $response->throw()->json();
    }

    public function getClient(): PendingRequest
    {
        return $this->httpClient;
    }

    protected function buildHttpClient(): void
    {
        $this->httpClient = Http::baseUrl(sprintf('%s/api', config('psb.base_url')))->withHeaders($this->headers);
    }

    protected function withMainHeaders(): static
    {
        $this->httpClient->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        return $this;
    }

    public function authenticateAs(string $token): static
    {
        $this->httpClient->withHeaders(['Authorization' => "Bearer $token"]);

        return $this;
    }
}
