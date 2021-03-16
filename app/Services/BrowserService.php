<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class BrowserService
{

    public function __construct()
    {

    }

    public function  open($url)
    {
        return $this->runCommand(sprintf("%s %s", $this->getSystemCommand(), $url));
    }

    public function getSystemCommand(): string
    {
        switch (PHP_OS) {
            case 'Darwin':
                $opener = 'open';
                break;
            case 'WINNT':
                $opener = 'start';
                break;
            default:
                $opener = 'xdg-open';
        }
        return $opener;
    }

    public function runCommand($command): bool|string|int
    {
        return shell_exec($command);
    }
}
