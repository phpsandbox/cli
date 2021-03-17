<?php

namespace App\Services;

class BrowserService
{
    public function  open(string $url)
    {
         $this->runCommand(sprintf("%s %s", $this->getSystemCommand(), $url));
    }

    public function getSystemCommand(): string
    {
        return match (PHP_OS) {
            'Darwin' => 'open',
            'WINNT' => 'start',
            default => 'xdg-open',
        };
    }

    public function runCommand($command)
    {
         shell_exec($command);
    }
}
