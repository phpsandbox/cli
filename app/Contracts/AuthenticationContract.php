<?php

namespace App\Contracts;

use App\Commands\Auth\Auth;

interface AuthenticationContract
{
    public function check(): bool;

    public function tokenIsValid($token): bool;

    public function retrieveToken(): string;

    public function setUpNewToken(Auth $command);


}
