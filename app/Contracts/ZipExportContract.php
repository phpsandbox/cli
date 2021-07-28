<?php

namespace App\Contracts;

use App\Services\ZipExportService;

interface ZipExportContract
{
    /**
     * @return bool | string
     */
    public function compress();

    public function setWorkingDir(?string $path): ZipExportService;

    public function cleanUp(): void;

    public function openNotebook(array $details, string $token): string;

    /**
     * @param string $filepath
     * @param string $token
     * @return mixed
     */
    public function upload(string $filepath, string $token = '');
}
