<?php

namespace App\Tasks;

use Hhxsv5\LaravelS\Swoole\Task\Task;

class TestTask extends Task
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

    //  可选的，完成事件，任务处理完后的逻辑，运行在Worker进程中，可以投递任务
    public function finish() {
        \Log::info(__CLASS__ . ':finish start', [$this->result]);

        //  完成task1后，投递其他任务
        Task::deliver(new Test2Task("task2"));
    }
}