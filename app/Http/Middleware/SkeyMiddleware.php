<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use App\WxUser;
use Illuminate\Support\Facades\Log;

class SkeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $sessionId = $request->header('sessionId', '');

        if (!$sessionId)
        {
            // 缺少必要参数
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $wxUserModel = new WxUser();

        $userId = $wxUserModel->getUserIdBySessionId($sessionId);

        if (!$userId)
        {

            Log::error('sessionId过期：' . $sessionId);
            // sessionId已过期，重新登录
            return response()->json(Config::get('constants.SESSIONID_EXP_ERROR'));
        }

        // 添加userId
        $request->attributes->add(['userId' => $userId]);

        return $next($request);
    }
}
