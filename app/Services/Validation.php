<?php

namespace App\Services;

use Composer\Json\JsonFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Seld\JsonLint\ParsingException;
use UnexpectedValueException;

class Validation
{
    protected string $directory;

    protected Collection $errors;

    public function __construct()
    {
        $this->errors = collect([]);
    }

    public function validate(string $directory, array $rules): bool
    {
        $this->directory = $directory;
        $result = [];

        foreach ($rules as $rule) {
            [$command, $option] = $this->parseRule($rule);
            $result[] = $this->{$command}($option);
        }

        return ! in_array(false, $result);
    }

    protected function parseRule(string $rule): array
    {
        $pieces = explode(':', $rule);

        return strpos($rule, ':') === false
                ? ['validate' . Str::studly($rule), null]
                : ['validate' . Str::studly($pieces[0]), $pieces[1]];
    }

    protected function addError(string $error): void
    {
        $this->errors->push($error);
    }

    public function errors(): array
    {
        return $this->errors->toArray();
    }

    protected function validateHasComposer(): bool
    {
        if (file_exists($this->directory . DIRECTORY_SEPARATOR . 'composer.json')) {
            return true;
        }
        $this->addError('Composer.json is missing in the project root directory');

        return false;
    }

    protected function validateComposerIsValid(): bool
    {
        if (! $this->validateHasComposer()) {
            return false;
        }

        try {
            JsonFile::parseJson(file_get_contents($this->directory . DIRECTORY_SEPARATOR . 'composer.json'));

            return true;
        } catch (UnexpectedValueException | ParsingException $e) {
            $this->addError('the composer.json file is invalid');

            return false;
        }
    }

    protected function validateSize($file): bool
    {
        if (File::fileSizeInMB($file) > (float) config('psb.max_file_size')) {
            $this->addError(sprintf('File exceeds the upload limit of %s MB', config('psb.max_file_size')));

            return false;
        }

        return true;
    }
}
