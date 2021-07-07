<?php

namespace App\Traits;

use Closure;

trait Multitask
{
    protected array $tasks;

    protected function multiTask(string $title, Closure $tasks): bool
    {
        $tasks();
        $this->info(sprintf('%s : starting', $title));

        foreach ($this->tasks as   $task) {
            $currentTask = $this->task($task['title'], $task['action']);
            if ($currentTask !== true) {
                $this->info(sprintf('%s : failed', $title));

                return false;
            }
        }
        $this->info(sprintf('%s : completed', $title));

        return true;
    }

    protected function tasks($title, Closure $action): void
    {
        $this->tasks[] = ['title' => $title, 'action' => $action];
    }
}
