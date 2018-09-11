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

    /**
     * 通过sessionId获取userId
     * @param string $sessionId
     * @return bool|string
     */
    function getSKeyBySessionId($sessionId = '')
    {
        if (!$sessionId) return false;

        $sessionIdKey = $this->getUserSessionIdKey($sessionId);

        if (!Redis::exists($sessionIdKey)) return false;

        $sessionKey = Redis::hget($sessionIdKey, 'session_key');

        if (!$sessionKey) return false;

        return $sessionKey;
    }

    function setUserSessionId($key, $data = [])
    {

        if (!$key || !$data) return false;

        Redis::hmset($key, $data);

        Redis::expireAt($key, Carbon::parse('+30 days')->startOfDay()->timestamp);

        return true;
    }

    /**
     * 获取操作时间
     * @param string $userId
     * @return bool
     */
    function getUserOpTs($userId = '')
    {

        if (!$userId) return false;

        $key = $this->getUserOpTsKey($userId);

        return Redis::get($key);
    }

    /**
     * 设置操作时间
     * @param string $userId
     * @param string $ts
     * @return bool
     */
    function setUserOpTs($userId = '', $ts = '')
    {

        if (!$userId || !$ts) return false;

        $key = $this->getUserOpTsKey($userId);

        Redis::set($key, $ts);

        Redis::expireAt($key, Carbon::now()->endOfDay()->timestamp);

        return true;
    }

    function getUserSessionIdKey($sessionId = '')
    {
        return 'SID_' . $sessionId;
    }

    function getUserOpTsKey($userId = '')
    {
        return 'U_OPTS_' . $userId;
    }


}
