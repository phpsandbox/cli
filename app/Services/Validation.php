<?php


namespace App\Services;


use Composer\Json\JsonFile;
use Seld\JsonLint\ParsingException;
use UnexpectedValueException;

class Validation
{
    protected $rules = [
        'hasComposer' => 'validateHasComposer',
        'composerIsValid' => 'validateComposerIsValid',
        'size' => 'validateFileSize'
    ];

    protected $directory;

    protected $errors = [];


    public function validate($directory, $rules)
    {
        $this->directory = $directory;
        $result = [];

        foreach ($rules as $rule) {
            [$command, $option] = $this->parseRule($rule);
            $result[] = $this->{$this->rules[$command]}($option);
        }

        return !in_array(false, $result);
    }

    protected function parseRule($rule)
    {
        $parse = explode(',', $rule);

        if (count($parse) < 2) {
            return [$rule, ''];
        }

        return $parse;
    }

    public function errors()
    {
        return $this->errors;
    }

    protected function validateHasComposer()
    {
        if (file_exists($this->directory . DIRECTORY_SEPARATOR . 'composer.json')) {
            return true;
        }
        $this->errors[] = 'Composer.json is missing in the project root directory';
        return false;
    }

    protected function validateComposerIsValid()
    {
        if (!file_exists($this->directory . DIRECTORY_SEPARATOR . 'composer.json')) {
            $this->errors[] = 'composer.json file not found';
            return false;
        }

        try {
            JsonFile::parseJson(file_get_contents($this->directory . DIRECTORY_SEPARATOR . 'composer.json'));
            return true;
        } catch (UnexpectedValueException $e) {
            $this->errors[] = 'the composer.json file is invalid';
            return false;
        } catch (ParsingException $e) {
            $this->errors[] = 'the composer.json file is invalid';
            return false;
        }

    }

    protected function validateFileSize($file)
    {
        $file_size = filesize($file) / 1024;
        if ($file_size > $maxFileSize = config('psb.max_file_size')) {
            $this->errors[] = sprintf('File execeeds the upload limit of %s MB', $maxFileSize/1024);
            return false;
        }
        return true;
    }

}
