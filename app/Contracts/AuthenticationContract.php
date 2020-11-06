<?php

namespace App\Contracts;


interface AuthenticationContract
{
    public function check(): bool;

    public function tokenIsValid(String $token): bool;

    public function retrieveToken(): string;

}
