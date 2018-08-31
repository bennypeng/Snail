<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
            \Redis::hmset($data['openId'], $data);
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

        if (\Redis::exists($openId))
        {
            return \Redis::hgetall($openId);
        }

        $userInfo = WxUser::where('openId', $openId)->first();

        if (!$userInfo) return array();

        \Redis::hmset($openId, $userInfo);

        return $userInfo;
    }

    function setUserSessionKey($key, $data = [])
    {
        if (!$key || !$data) return false;
        \Redis::hmset($key, $data);
        \Redis::expire($key, 86400 * 30);
        return true;
    }

    function getUserSessionKey($sessionId = '')
    {
        return 'SKEY_' . $sessionId;
    }


}
