<?php


namespace App\Http;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\ConnectionException;
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


    public function __construct()
    {
        $this
            ->setRedirectToBrowserUrl()
            ->setFetchCliTokenUrl()
            ->setFileUploadUrl()
            ->setFetchAuthUserUrl();

    }

    protected function setFileUploadUrl()
    {
        $this->fileUploadUrl = config('psb.file_upload_url');
        return $this;
    }

    protected  function  setRedirectToBrowserUrl()
    {
        $this->redirectToBrowserUrl = config('psb.token_url');
        return $this;
    }

    protected function setFetchCliTokenUrl()
    {
        $this->fetchCliTokenUrl = config('psb.validate_token_url');
        return $this;
    }


    public function setFetchAuthUserUrl()
    {
        $this->fetchAuthUserUrl = config('psb.fetch_auth_user_url');
    }


    public function fetchCliToken($access_token)
    {
            $response = $this->withMainHeaders()->getClient()->post($this->fetchCliTokenUrl,['code'=>$access_token]);
            $response->throw();
            return ($response->body());
    }

    public function getAuthenticatedUser($token)
    {

        $response = $this->withMainHeaders()->authenticateAs($token)->getClient()->get($this->fetchAuthUserUrl);
        $response->throw();
        return $response->status();
    }

    protected function getCompleteFilePath($filePath)
    {
        $base = config('psb.files_storage');
        return $base.DIRECTORY_SEPARATOR.$filePath;
    }

    public function uploadCompressedFile($file_path, $token)
    {
        $client = $token != ''
            ? $this->authenticateAs($token)->getClient()
            : $this->getClient();

        $response = $client->asForm()->post(
            $this->fileUploadUrl,[
                'multipart'=>[
                    'name'=>'archive',
                    'contents'=>fopen($this->getCompleteFilePath($file_path),'r')
                ]
            ]
        );

        $response->throw();
        return $response->body();
    }



    public function getClient()
    {
        return Http::withHeaders($this->headers);
    }

    protected function setHeader($type , $value)
    {
        $this->headers[$type] = $value;
    }

    protected function withMainHeaders()
    {
        $this->setHeader('Content-Type','application/json');
        $this->setHeader('accept','application/json');
        return $this;
    }

    public function authenticateAs($token)
    {
        $this->setHeader('authorization','Bearer '.$token);
        return $this;
    }


}
