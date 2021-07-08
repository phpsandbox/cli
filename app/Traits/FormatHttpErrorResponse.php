<?php

namespace App\Traits;

use Illuminate\Http\Client\RequestException;

trait FormatHttpErrorResponse
{
    public function formatError(RequestException $e, string $errorMsg = ''): string
    {
        switch ($e->getCode()) {
            case $e->response->serverError():
                return $this->showServerError();
                break;
            case 422:
                return  $this->showValidationError($e);
                break;
            case 401:
                return $this->showUnauthenticatedError();
                break;
            case 404:
                return $this->missingResource($errorMsg);
                break;
            default: return 'An error occurred';
        }
    }

    protected function showServerError(): string
    {
        return 'Could not complete request. Kindly raise an issue if it persist.';
    }

    protected function showValidationError(RequestException $e)
    {
        return $e->getMessage();
    }

    protected function showUnauthenticatedError(): string
    {
        return 'You are not authenticated to make this request.';
    }

    protected function missingResource(string $errorMsg = ''): string
    {
        return $errorMsg == '' ? 'Something went wrong, please try again.' : $errorMsg;
    }
}
