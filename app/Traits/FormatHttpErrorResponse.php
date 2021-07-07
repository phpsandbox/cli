<?php

namespace App\Traits;

use Illuminate\Http\Client\RequestException;

trait FormatHttpErrorResponse
{
    public function formatError(RequestException $e, string $errorMsg = ''): string
    {
        return match ($e->getCode()) {
            $e->response->serverError() => $this->showServerError(),
            422 => $this->showValidationError($e),
            401 => $this->showUnauthenticatedError(),
            404 => $this->missingResource($errorMsg),
            default => 'An error occurred',
        };
    }

    public function showError(RequestException $e, string $errorMsg = ''): string
    {
        return match ($e->getCode()) {
            $e->response->serverError() => $this->showServerError(),
            422 => $this->showValidationError($e),
            401 => $this->showUnauthenticatedError(),
            404 => $this->missingResource($errorMsg),
            default => 'An error occurred',
        };
    }

    public function couldNotConnect(): void
    {
        $this->error('Could not establish a connection. Kindly check that your computer is connected to the internet.');
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
