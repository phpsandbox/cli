<?php

namespace App\Traits;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;

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
                return $this->showMissingResource($errorMsg);
                break;
            case 403:
                return $this->showAuthorizationError();
            default: return 'An error occurred';
        }
    }

    protected function showServerError(): string
    {
        return 'The server responded with a 500 error. Kindly raise an issue if it persist.';
    }

    protected function showValidationError(RequestException $e): string
    {
        $getErrors = collect(Arr::get($e->response->json(), 'errors'));
        $getErrors = $getErrors->map(function (array $errors, string $index) {
            return $errors;
        })->flatten(1)->toArray();

        return implode("\n", $getErrors);
    }

    protected function showUnauthenticatedError(): string
    {
        return 'The server responded with a 401 error, You are not authenticated to make this request.';
    }

    protected function showMissingResource(string $errorMsg = ''): string
    {
        return $errorMsg == '' ? 'Something went wrong, please try again.' : $errorMsg;
    }

    protected function showAuthorizationError()
    {
        return ' The server responded with a 403 error. You are not authorized to make this request.';
    }
}
