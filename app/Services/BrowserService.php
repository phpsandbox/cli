<?php

namespace App\Services;

class BrowserService
{
    public function open(string $url): void
    {
        $this->runCommand(sprintf('%s %s', $this->getSystemCommand(), $url));
    }

    public function getSystemCommand(): string
    {
        switch (PHP_OS) {
            case 'Darwin':
                return 'open';
                break;
            case 'WINNT':
                return 'start';
                break;
            default:
                return 'xdg-open';
        };
    }

    public function runCommand($command): void
    {
        shell_exec($command);
    }
}
