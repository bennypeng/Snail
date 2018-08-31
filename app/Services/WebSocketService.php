<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;

/**
 * @see https://wiki.swoole.com/wiki/page/400.html
 */
class WebSocketService implements WebSocketHandlerInterface
{

    // 声明没有参数的构造函数
    public function __construct() {}

    /**
     * 监听open事件
     * @param \swoole_websocket_server $server
     * @param \swoole_http_request $request
     */
    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        //  table使用, 设置值
        $userId = mt_rand(1000, 10000);
        app('swoole')->wsTable->set('uid:' . $userId, ['value' => $request->fd]);// 绑定uid到fd的映射
        app('swoole')->wsTable->set('fd:' . $request->fd, ['value' => $userId]);// 绑定fd到uid的映射
        Log::info(app('swoole')->wsTable->get('uid:' . $userId));

        // 在触发onOpen事件之前Laravel的生命周期已经完结，所以Laravel的Request是可读的，Session是可读写的
        Log::info('New WebSocket connection', [$request->fd, request()->all(), session()->getId(), session('xxx'), session(['yyy' => time()])]);
        foreach (app('swoole')->wsTable as $key => $row) {
            if (strpos($key, 'uid:') === 0) {
                $server->push($row['value'], json_encode(["uid" => 0, "content" => "用户 " . $userId . " 加入了聊天室"]));
            }
        }
    }

    /**
     * 监听message事件
     * @param \swoole_websocket_server $server
     * @param \swoole_websocket_frame $frame
     */
    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        $uid = app('swoole')->wsTable->get('fd:' . $frame->fd);

        //  获取table值并广播道客户端
        Log::info('Receive:', [$frame->data]);
        foreach (app('swoole')->wsTable as $key => $row) {
            if (strpos($key, 'uid:') === 0) {
                $server->push($row['value'], json_encode(["uid" => $uid['value'], "content" => $frame->data]));
            }
        }
    }

    /**
     * 监听close事件
     * @param \swoole_websocket_server $server
     * @param $fd
     * @param $reactorId
     */
    public function onClose(\swoole_websocket_server $server, $fd, $reactorId)
    {
        $uid = app('swoole')->wsTable->get('fd:' . $fd);
        if ($uid !== false) {
            app('swoole')->wsTable->del('uid:' . $uid['value']);// 解绑uid映射
        }
        app('swoole')->wsTable->del('fd:' . $fd);// 解绑fd映射

        // 广播
        foreach (app('swoole')->wsTable as $key => $row) {
            if (strpos($key, 'uid:') === 0) {
                $server->push($row['value'], json_encode(["uid" => 0, "content" => "用户 " . $uid['value'] . " 退出了聊天室"]));
            }
        }
    }

}