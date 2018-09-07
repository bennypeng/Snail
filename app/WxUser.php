<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class WxUser extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    //public    $timestamps = false;
    //protected $dateFormat = 'U';


    /**
     * 注册用户
     * @param array $data
     * @return bool
     */
    public function registerUser($data = [])
    {

        if (
            count($data) == 0
            || !isset($data['openId'])
            || !$data['openId']
        ) return false;

        $userId = WxUser::insertGetId($data);

        $data['id'] = $userId;

        if ($userId)
        {
            Redis::hmset($data['openId'], $data);
            return $userId;
        }

        return false;
    }

    /**
     * 通过openId获取用户信息
     * @param string $openId
     * @return array
     */
    function getUserByOpenId($openId = '')
    {

        if (!$openId) return array();

        if (Redis::exists($openId))
        {
            return Redis::hgetall($openId);
        }

        $userInfo = WxUser::where('openId', $openId)->first();

        if (!$userInfo) return array();

        Redis::hmset($openId, $userInfo->toArray());

        return $userInfo;
    }

    /**
     * 通过sessionId获取userId
     * @param string $sessionId
     * @return bool|string
     */
    function getUserIdBySessionId($sessionId = '')
    {
        if (!$sessionId) return false;

        $sessionIdKey = $this->getUserSessionIdKey($sessionId);

        if (!Redis::exists($sessionIdKey)) return false;

        $userId = Redis::hget($sessionIdKey, 'userId');

        if (!$userId) return false;

        return $userId;
    }

    function setUserSessionId($key, $data = [])
    {

        if (!$key || !$data) return false;

        Redis::hmset($key, $data);

        Redis::expire($key, Carbon::parse('+30 days')->startOfDay()->timestamp);

        return true;
    }

    function getUserSessionIdKey($sessionId = '')
    {
        return 'SID_' . $sessionId;
    }


}
