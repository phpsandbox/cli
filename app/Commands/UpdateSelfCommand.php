<?php

namespace App\Commands;

use LaravelZero\Framework\Components\Updater\SelfUpdateCommand;

class UpdateSelfCommand extends SelfUpdateCommand
{
    protected $name = 'update-self';

    protected $description = 'Update PHPSandbox cli to a latest release';
}
