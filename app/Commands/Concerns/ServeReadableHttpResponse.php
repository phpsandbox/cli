<?php

namespace App\Commands\Concerns;

use Illuminate\Http\Client\RequestException;

trait ServeReadableHttpResponse
{
    public function serveError(RequestException $e)
    {
        switch ($e->getCode()) {
            case $e->response->serverError():
                return $this->serverError();
                break;

            case 422:
                return $this->validationError($e);
                break;

            case 401:
                return $this->unauthenticated();
                break;

            case $e->response->clientError():
                return $this->clientError();
                break;

            default:
                return 'An error occured';
             break;
        }
    }

    public function serverError(): string
    {
        return 'Could not complete request. Kindly raise an issue if it persist.';
    }

    public function validationError(RequestException $e)
    {
        return $e->getMessage();
    }

    public function unauthenticated()
    {
        return 'You are not authenticated to make this request.';
    }

    public function clientError()
    {
        return 'Something went wrong, please try again.';
    }
}
