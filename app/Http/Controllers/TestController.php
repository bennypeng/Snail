<?php

namespace App\Http\Controllers;
use App\Contracts\HelperContract;
use App\Events\TestEvent;
use App\Tasks\TestTask;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    protected $helper;

    //  依赖注入
    public function __construct(HelperContract $helper)
    {
        $this->helper = $helper;
    }

    public function test1()
    {
        return $this->helper->getNextFifteenTs();
    }

    //  绑定在单例上
    public function test2()
    {
        $helper = App::make('helper');
        return $helper->getNextFifteenTs();
    }

    //  异步处理事件
    public function test3()
    {
        $success = Event::fire(new TestEvent('event test data'));
        return $success ? "ok" : "fail";
    }

    //  投放任务
    public function test4()
    {
        $task = new TestTask("task 1 data");
        $ret = Task::deliver($task);
        return json_encode($ret);
    }

    //  获取swoole状态
    public function test5()
    {
        $swoole = app('swoole');
        return json_encode($swoole->stats());
    }

    //  数据库
    public function test6()
    {
        $a = DB::select("SELECT * FROM users WHERE id=1");
        return response()->json($a);
    }

    //  Redis
    public function test7()
    {
        //\Redis::set('aaaaa' . rand(1, 10), '11111');
        return response(\Redis::get('aaaaa' . rand(1, 10)));
    }

    //  返回数据给vue前端页面测试
    public function test8()
    {
        return response()->json(
            [
                'result'   => [
                    ['name' => 'benny', 'age' => 27, 'date' => '1991-06-01'],
                    ['name' => 'peter', 'age' => 12, 'date' => '2006-01-25'],
                    ['name' => 'marry', 'age' => 16, 'date' => '2002-12-01'],
                    ['name' => 'jacket', 'age' => 80, 'date' => '1938-01-01'],
                    ['name' => 'max', 'age' => 50, 'date' => '1968-06-01'],
                    ['name' => 'john', 'age' => 40, 'date' => '1978-04-01'],
                    ['name' => 'lessi', 'age' => 30, 'date' => '1988-05-01'],
                ],
                'message' => '操作成功',
                'code' => 10000
            ]
        );
    }

}
