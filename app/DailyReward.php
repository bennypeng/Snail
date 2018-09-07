<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class DailyReward extends Model
{
    protected $table      = 'daily_rewards';
    protected $primaryKey = 'id';
    public    $timestamps = false;


    /**
     * 获取用户每日签到奖励列表
     * @param string $userId
     * @return array|bool
     */
    public function getUserDailyReward($userId = '')
    {
        if (!$userId) return false;

        $key = $this->_getUserDailyRewardKey($userId);

        if (!Redis::exists($key))
        {
            $dailyConfig = $this->getDailyRewardsConf();

            if (!$dailyConfig) return false;

            $userDaily = [];

            foreach($dailyConfig as $v)
            {
                $userDaily[$v['day']] = json_encode([
                    'day' => $v['day'],
                    'diamond' => $v['diamond'],
                    'status' => 0
                ]);
            }

            Redis::hmset($key, $userDaily);

            Redis::expireat($key, Carbon::parse('+7 days')->startOfDay()->timestamp);

        } else {
            $userDaily = Redis::hgetall($key);
        }

        if ($userDaily)
        {
            foreach($userDaily as $k => &$v) {
                $v = json_decode($v, true);
            }

            ksort($userDaily);
        }

        unset($v);

        return $userDaily;
    }

    /**
     * 获取每日奖励配置
     * @return array
     */
    public function getDailyRewardsConf()
    {
        $key = $this->_getDailyRewardKey();

        if (!Redis::exists($key))
        {
            $dailyConfigObj = DailyReward::where('day', '<=', 7)
                ->orderBy('day', 'asc')
                ->take(7)
                ->get();

            if (!$dailyConfigObj) return array();

            $dailyConfig = $dailyConfigObj->toArray();

            foreach($dailyConfig as $k => &$v) {
                $v = json_encode($v);
            }

            Redis::hmset($key, $dailyConfig);

        }

        unset($v);

        $dailyConfig = Redis::hgetall($key);

        foreach($dailyConfig as $k => &$v) {
            $v = json_decode($v, true);
        }

        unset($v);

        return $dailyConfig;
    }

    /**
     * 更新用户签到数据
     * @param string $userId
     * @param string $day
     * @param array $data
     * @return bool
     */
    public function setUserDailyReward($userId = '', $day = '', $data = [])
    {
        if (!$userId || !$day || !$data) return false;

        $key = $this->_getUserDailyRewardKey($userId);

        Redis::hset($key, $day, json_encode($data));

        return true;
    }

    /**
     * 获取用户签到状态
     * @param string $userId
     * @return bool|int|string
     */
    public function getUserDailyStatus($userId = '')
    {
        if (!$userId) return false;

        $key = $this->_getUserDailyCheckKey($userId);

        if (!Redis::exists($key)) {

            Redis::set($key, 0);

            Redis::expireat($key, Carbon::now()->endOfDay()->timestamp);
        }

        return Redis::get($key);
    }

    /**
     * 设置签到状态
     * @param string $userId
     * @return bool
     */
    public function setUserDailyStatus($userId = '')
    {
        if (!$userId) return false;

        $key = $this->_getUserDailyCheckKey($userId);

        Redis::set($key, 1);

        return true;
    }


    private function _getUserDailyRewardKey($userId = '')
    {
        return 'DR_' . $userId;
    }

    private function _getDailyRewardKey()
    {
        return 'CONFIG_DAILY_REWARDS';
    }

    private function _getUserDailyCheckKey($userId = '')
    {
        return 'DR_CHECK_' . $userId;
    }


}
