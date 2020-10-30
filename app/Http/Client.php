<?php

namespace App\Http;
use Illuminate\Support\Facades\Http;

class Client
{
    protected  $headers = [];

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
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */

    private $fileUploadUrl;

    /**
     * @var \Illuminate\Http\Client\PendingRequest
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

    protected function setFileUploadUrl()
    {
        $this->fileUploadUrl = '/cli/import';
        return $this;
    }

    protected function setRedirectToBrowserUrl()
    {
        $this->redirectToBrowserUrl = sprintf('%s/login/cli', config('psb.base_url'));
        return $this;
    }

    protected function setFetchCliTokenUrl()
    {
        $this->fetchCliTokenUrl = 'login/cli';
        return $this;
    }


    public function setFetchAuthUserUrl()
    {
        $this->fetchAuthUserUrl = '/user';
    }


    public function fetchCliToken($access_token)
    {
            $response = $this->withMainHeaders()->getClient()->post($this->fetchCliTokenUrl, ['code'=> $access_token]);
            return $response->throw()->json()['token'];
    }

    public function getAuthenticatedUser($token)
    {
        $response = $this->withMainHeaders()->authenticateAs($token)->getClient()->get($this->fetchAuthUserUrl);
        $response->throw();
        return $response->status();
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

    public function getClient()
    {
        return $this->httpClient;
    }

    protected function buildHttpClient()
    {
        $this->httpClient = Http::baseUrl(sprintf('%s/api', config('psb.base_url')))->withHeaders($this->headers);
    }

    protected function setHeader($type , $value)
    {
        $this->headers[$type] = $value;
    }

    protected function withMainHeaders()
    {
        $this->httpClient->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        return $this;
    }

    public function authenticateAs($token)
    {
        $this->httpClient->withHeaders(['Authorization' => "Bearer $token"]);
        return $this;
    }
}
