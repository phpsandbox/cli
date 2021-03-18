<?php


namespace App\Services;


use Composer\Json\JsonFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Seld\JsonLint\ParsingException;
use UnexpectedValueException;

class Validation
{
    protected array $rules = [
        'hasComposer' => 'validateHasComposer',
        'composerIsValid' => 'validateComposerIsValid',
        'size' => 'validateFileSize'
    ];

    protected string $directory;

    protected array $errors = [];


    public function validate(string $directory, array $rules): bool
    {
        $this->directory = $directory;
        $result = [];

        foreach ($rules as $rule) {
            [$command, $option] = $this->parseRule($rule);
            $result[] = $this->{$this->rules[$command]}($option);
        }

        return !in_array(false, $result);
    }

    protected function parseRule($rule): array
    {
        $parse = explode(',', $rule);

        if (count($parse) < 2) {
            return [$rule, ''];
        }

        return $parse;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    protected function validateHasComposer(): bool
    {
        if (file_exists($this->directory . DIRECTORY_SEPARATOR . 'composer.json')) {
            return true;
        }
        $this->errors[] = 'Composer.json is missing in the project root directory';
        return false;
    }

    protected function validateComposerIsValid(): bool
    {
        if (!$this->validateHasComposer()) return false;

        try {
            JsonFile::parseJson(file_get_contents($this->directory . DIRECTORY_SEPARATOR . 'composer.json'));
            return true;
        } catch (UnexpectedValueException | ParsingException $e) {
            $this->errors[] = 'the composer.json file is invalid';
            return false;
        }

    }

    protected function validateFileSize($file): bool
    {
        if (File::fileSizeInMB($file) > (float) config('psb.max_file_size')) {
            $this->errors[] = sprintf('File exceeds the upload limit of %s MB', config('psb.max_file_size'));
            return false;
        }
        return true;
    }
}
