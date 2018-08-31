<?php

namespace App\Processes;

use Hhxsv5\LaravelS\Swoole\Process\CustomProcessInterface;
use Illuminate\Support\Facades\Log;

class TestProcess implements CustomProcessInterface
{
    public static function getName()
    {
        //  进程名称
        return 'test';
    }

    public static function isRedirectStdinStdout()
    {
        //  是否重定向输入输出
        return false;
    }

    public static function getPipeType()
    {
        //  管道类型：0不创建管道，1创建SOCK_STREAM类型管道，2创建SOCK_DGRAM类型管道
        return 0;
    }

    public static function callback(\swoole_server $swoole)
    {
        Log::info(__METHOD__, [posix_getpid(), $swoole->stats()]);
        while (true) {
            sleep(2);
            Log::info('Do something' . date('Y-m-d H:i:s'));
        }
    }
}