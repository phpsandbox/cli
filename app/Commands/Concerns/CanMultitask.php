<?php


namespace App\Commands\Concerns;


use Closure;

trait CanMultitask
{
    protected array $tasks;

    protected function multipleTask()
    {
        $args = func_get_args();
        $this->info( sprintf('%s : starting',$title = $args[0]));
        $taskTitles = $args[1];
        unset($args[0]);
        unset($args[1]);

        foreach (array_values($args) as $key => $task)
        {
            $currentTask = $this->task($taskTitles[$key],$task);
            if ($currentTask !== true){
                $this->info(sprintf('%s : failed',$title));
                exit(1);
            }
        }
        $this->info(sprintf('%s : completed',$title));
    }

    protected function multiTask(string $title, $tasks)
    {
        $tasks();
        $this->info( sprintf('%s : starting',$title));

        foreach ($this->tasks as   $task)
        {
            $currentTask = $this->task($task['title'], $task['action']);
            if ($currentTask !== true){
                $this->info(sprintf('%s : failed',$taskTitle));
                exit(1);
            }
        }
        $this->info(sprintf('%s : completed',$title));
    }

    protected function tasks($title,  $action)
    {
        $this->tasks[] = ["title" => $title, "action" => $action];
    }

}
