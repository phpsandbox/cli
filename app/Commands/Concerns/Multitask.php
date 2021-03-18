<?php


namespace App\Commands\Concerns;


use Closure;

trait Multitask
{
    protected array $tasks;

    protected function multiTask(string $title, Closure $tasks)
    {
        $tasks();
        $this->info( sprintf('%s : starting',$title));

        foreach ($this->tasks as   $task)
        {
            $currentTask = $this->task($task['title'], $task['action']);
            if ($currentTask !== true){
                $this->info(sprintf('%s : failed',$task['title']));
                exit(1);
            }
        }
        $this->info(sprintf('%s : completed',$title));
    }

    protected function tasks($title,  Closure $action)
    {
        $this->tasks[] = ["title" => $title, "action" => $action];
    }

}
