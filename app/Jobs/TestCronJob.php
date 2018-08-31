<?php

namespace App\Jobs;

use App\Tasks\TestTask;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\Timer\CronJob;
use Illuminate\Support\Facades\Log;

class TestCronJob extends CronJob
{
    protected $i = 0;

    //  声明没有参数的构造函数
    public function __construct()
    {
    }

    public function interval()
    {
        //  每1秒运行一次
        return 1000;
    }

    public function isImmediate()
    {
        //  是否立即执行第一次，false则等待间隔时间后执行第一次
        return false;
    }

    public function run()
    {
        Log::info(__METHOD__ , ['start', $this->i, microtime(true)]);

        /**
         * @todo
         */
        $this->i++;

        Log::info(__METHOD__, ['end', $this->i, microtime(true)]);

        //  执行5次，终止任务
        if ($this->i >= 5) {
            Log::info(__METHOD__, ['stop', $this->i, microtime(true)]);

            //  终止任务
            $this->stop();

            // CronJob中也可以投递Task，但不支持Task的finish()回调。
            // 参数2需传true
            // config/laravels.php中修改配置task_ipc_mode为1或2，参考 https://wiki.swoole.com/wiki/page/296.html
            $ret = Task::deliver(new TestTask('test 1 data'), true);
            var_dump($ret);
        }
    }
}
