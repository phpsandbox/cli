<?php


namespace App\Services;


class Validation
{
    protected  $rules = [
        'isPHPDIR' => 'validateHasComposer',
        'validFileSize' => 'validateFileSizeValid'
    ];
    public function __construct()
    {

    }

    public function validate($dir , $rules)
    {
        $this->dir = $dir;
        $this->rules = $rules;

    }

    protected function validateHasComposer()
    {
        return file_exists($this->dir.'composer.json');
    }

}
