<?php

namespace App\Services;

class BrowserService
{

    /**
     * Opens User Default Browser
     *
     * @param $url
     */
    public function  open($url): void
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

        exec(sprintf('%s %s', $opener, $url));
    }
}
