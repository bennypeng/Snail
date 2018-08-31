<?php

namespace App\Tasks;

use Hhxsv5\LaravelS\Swoole\Task\Task;

class Test2Task extends Task
{
    private $data;
    private $result;

    public function __construct($data)
    {
        $this->data = $data;
    }

    //  处理任务的逻辑，运行在Task进程中， 不能投递任务
    public function handle()
    {
        \Log::info(__CLASS__ . ':handle start', [$this->data]);

        //  模拟慢速
        sleep(10);
        $this->result = "the result of " . $this->data;
    }
}