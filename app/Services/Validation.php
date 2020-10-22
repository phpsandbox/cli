<?php


namespace App\Services;


use Composer\Json\JsonFile;
use Seld\JsonLint\ParsingException;

class Validation
{
    protected  $rules = [
        'hasComposer' => 'validateHasComposer',
        'composerIsValid' => 'validateComposerIsValid',
        'size'=>'validateFileSize'
    ];

    protected  $directory;


    protected  $errors;

    public function __construct()
    {

    }

    protected function parseRule($rule)
    {
        $parse = explode(':',$rule);

        if (count($parse) < 2)
        {
            return [$rule,''];
        }
        return $parse;
    }

    public function validate($directory , $rules)
    {
        $this->directory = $directory;

        $result = [];
        foreach($rules as $rule)
        {

            [$command , $option] = $this->parseRule($rule);
            $result[] = $this->{$this->rules[$command]}($option);
        }
        return !in_array(false,$result);

    }

    protected function validateHasComposer()
    {
        if(file_exists($this->directory.DIRECTORY_SEPARATOR.'composer.json'))
        {
            return true;
        }
        $this->errors[] = 'Composer.json is missing in the project root directory';
    }

    protected function validateComposerIsValid()
    {
       if (!file_exists($this->directory.DIRECTORY_SEPARATOR.'composer.json'))
       {
           $this->errors[] = 'composer.json file not found';
           return false;
       }
       try
       {
           JsonFile::parseJson(file_get_contents($this->directory.DIRECTORY_SEPARATOR.'composer.json'));
            return true;
       }
       catch(\UnexpectedValueException $e)
       {
           $this->errors[] = 'the composer.json file is invalid';
           return false;

       }
       catch (ParsingException $e)
       {
           $this->errors[] = 'the composer.json file is invalid';
           return false;
       }

    }

    protected function validateFileSize($file)
    {
        $file = config('psb.files_storage').DIRECTORY_SEPARATOR.$file;

        if(!file_exists($file))
        {
            $this->errors[] = 'An error occured';
            return false;
        }
        $file_size = filesize($file)/1024;
        if ($file_size > config('psb.max_file_size'))
        {
            $this->errors[] = 'File execeeds the upload limit';
            return false;
        }
        return true;
    }

    public function errors()
    {
        return $this->errors;
    }

}
