<?php


namespace App\Commands\Concerns;


trait CanMultitask
{
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

}
