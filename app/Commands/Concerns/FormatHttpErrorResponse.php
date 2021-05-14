<?php

namespace App\Commands\Concerns;

use Illuminate\Http\Client\RequestException;

trait FormatHttpErrorResponse
{
    public function showError(RequestException $e): string
    {
        return match ($e->getCode()) {
            $e->response->serverError() => $this->showServerError(),
            422 => $this->showValidationError($e),
            401 => $this->showUnauthenticatedError(),
            $e->response->clientError() => $this->showClientError(),
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

    protected function showClientError(): string
    {
        return 'Something went wrong, please try again.';
    }
}
