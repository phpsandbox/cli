<?php


namespace App\Contracts;


use Closure;

interface ImportNotebookContract
{
    public function downloadNotebookZip(Closure $progressCallback);
}
