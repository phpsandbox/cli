<?php

namespace App\Commands\Config;

use LaravelZero\Framework\Commands\Command;

class Init extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config:init {--output=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Creates a PHPSandbox config in the current work directory.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $template = $this->output->choice('Which notebook template will the project be identified as?', [
            'Laravel Notebook' => 'laravel',
            'Symfony' => 'symfony',
            'Slim' => 'slim',
            'Standard' => 'standard',
            'ReactPHP' => 'react-php-http',
        ]);

        $this->info("You have selected $template");

        if ($this->option('output')) {
            $this->output->writeln(json_encode(['template' => $template]));
        }
    }
}
