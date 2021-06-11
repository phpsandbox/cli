<?php

namespace App\Contracts;

use App\Services\ZipExportService;

interface ZipExportContract
{
    public function compress(): bool | string;

    public function setWorkingDir(?string $path): ZipExportService;

    public function cleanUp(): void;

    public function openNotebook(array $details, string $token): string;

    public function upload($filepath, $token = ''): mixed;
}
