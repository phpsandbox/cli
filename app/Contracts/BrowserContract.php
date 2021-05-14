<?php

namespace App\Contracts;

interface BrowserContract
{
    public function open(string $val): void;
}
