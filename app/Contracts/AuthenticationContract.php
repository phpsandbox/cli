<?php

namespace App\Contracts;

interface AuthenticationContract
{
    public function check(): bool;

    public function tokenIsValid(string $token): bool;

    public function retrieveToken(): string;

    public function launchBrowser(): void;

    public function fetchCliToken(string $access_token): string;

    public function storeNewToken(string $token): void;

    public function logout(): bool;
}
