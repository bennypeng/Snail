<?php

namespace App\Listeners;

use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
use Illuminate\Support\Facades\Log;

class TestListener extends Listener
{
    //  声明没有参数的构造函数
    public function __construct()
    {

    }

    public function handle(Event $event)
    {
        //  模拟慢速事件处理
        sleep(10);
        Log::info(__CLASS__ . ':handle start', [$event->getData()]);
    }
}
