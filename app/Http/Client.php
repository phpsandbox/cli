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



    public function __construct()
    {
        $this
            ->setRedirectToBrowserUrl()
            ->setFetchCliTokenUrl()
            ->setFetchAuthUserUrl();

    }

    protected  function  setRedirectToBrowserUrl()
    {
        $this->redirectToBrowserUrl = config('psb.TOKEN_URI');
        return $this;
    }

    protected function setFetchCliTokenUrl()
    {
        $this->fetchCliTokenUrl = config('psb.VALIDATE_TOKEN_URI');
        return $this;
    }


    public function setFetchAuthUserUrl()
    {
        $this->fetchAuthUserUrl = config('psb.FETCH_AUTH_USER_URL');
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
