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
     * @var string
     */
    protected $fetchCliTokenUrl = 'cli/login';

    /**
     * @var string
     */
    protected $fetchAuthUserUrl = '/user';

    /**
     * @var string
     */
    private $fileUploadUrl = '/cli/import';

    /**
     * @var PendingRequest
     */
    private $httpClient;

    public function __construct()
    {
        $this->buildHttpClient();
    }

    public function getClient(): PendingRequest
    {
        return $this->httpClient;
    }

    protected function buildHttpClient(): void
    {
        $this->httpClient = Http::baseUrl(sprintf('%s/api', config('psb.base_url')));
    }

    protected function withMainHeaders(): Client
    {
        $this->httpClient->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        return $this;
    }

    public function fetchCliToken(string $access_token): string
    {
        $response = $this->withMainHeaders()->getClient()->post($this->fetchCliTokenUrl, ['code' => $access_token]);
        return $response->throw()->json()['token'];
    }

    public function getAuthenticatedUser(string $token): bool
    {
        $response = $this->withMainHeaders()->authenticateAs($token)->getClient()->get($this->fetchAuthUserUrl);

        return $response->throw()->successful();
    }

    public function downloadNotebook(string $uniqueId, Closure $progressCallback): string
    {
        return $this->withMainHeaders()->getClient()->withOptions([
            'sink' => config('psb.files_storage') . "/$uniqueId.zip",
            'progress' => $progressCallback,
        ])->get("/notebook/download/$uniqueId")->throw()->body();
    }

    public function uploadCompressedFile(string $file_path, string $token): array
    {
        $client = $token != ''
            ? $this->authenticateAs($token)->getClient()
            : $this->getClient();

        $response = $client->attach('archive', fopen($file_path, 'r'))
            ->post($this->fileUploadUrl);

        return $response->throw()->json();
    }

    public function authenticateAs(string $token): Client
    {
        $this->httpClient->withHeaders(['Authorization' => "Bearer $token"]);

        return $this;
    }
}
