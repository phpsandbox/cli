<?php


namespace App\Services;


class BrowserService
{

    /**
     * Opens User Default Browser
     *
     * @param $authUri
     */
    public function  open($authUri){

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
            $opener = 'start';

         exec(sprintf('%s %s', $opener, $authUri));

    }
}
