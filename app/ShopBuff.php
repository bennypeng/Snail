<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class ShopBuff extends Model
{
    protected $table      = 'shop_buffs';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    /**
     * 获取增益商店配置
     * @return array
     */
    public function getBuffShopConf()
    {
        $key = $this->_getBuffShopKey();

        if (!Redis::exists($key))
        {
            $shopConfigObj = ShopBuff::where('id', '<=', 6)
                ->orderBy('id', 'asc')
                ->take(7)
                ->get();

            if (!$shopConfigObj) return array();

            $shopConfig = $shopConfigObj->toArray();

            foreach($shopConfig as $k => &$v) {
                $v = json_encode($v);
            }

            Redis::hmset($key, $shopConfig);
        }

        unset($v);

        $shopConfig = Redis::hgetall($key);

        foreach($shopConfig as $k => &$v) {
            $v = json_decode($v, true);
        }

        unset($v);

        ksort($shopConfig);

        return $shopConfig;
    }


    /**
     * 结算用户离线收益, 并设置领取时间
     * @param string $userId
     * @return int
     */
    public function settleBuffOfflineGold($userId = '')
    {
        if (!$userId) return 0;

        $userBuff = $this->getUserBuff($userId);

        if (!$userBuff) return 0;

        $snailModel = new \App\Snail();
        $userBagModel = new \App\UserBag();

        $earnGold = 0;
        $data = [
            'snailEarnPerSec' => 0,
            'offlineBuffSec' => 0,
            'doubleBuffSec' => 0
        ];
        $curTime = Carbon::now()->timestamp;
        $userBag = $userBagModel->getUserBag($userId, true);

        foreach ($userBuff as $k => &$v) {

            // buff已结束
            if ($v['endTime'] <= $curTime && $v['recTime'] >= $v['endTime']) {
                continue;
            }

            // 计算有收益的秒数
            $offsetSec = $v['endTime'] > $curTime
                ? $curTime - $v['recTime']
                : $v['endTime'] - $v['recTime'];

            if ($offsetSec <= 0) continue;

            // 变更领取时间
            $v['recTime'] = $curTime;

            // 计算离线buff
            if ($v['buffType'] == 2) {

                $data['snailEarnPerSec'] = $snailModel->calcSnailEarn($userBag['snailMap']);

                $data['offlineBuffSec'] = $offsetSec;

            } else if ($v['buffType'] == 1) {

                // 计算X2 buff
                $data['doubleBuffSec'] = $offsetSec;
            }
        }

        unset($v);

        if (!$data['snailEarnPerSec']) return 0;

        // X2 buff 也当作离线收益来计算
        if ($data['doubleBuffSec'])
        {
            $earnGold += ($data['doubleBuffSec'] * $data['snailEarnPerSec']);

            if ($data['offlineBuffSec'] < $data['doubleBuffSec'])
            {
                $data['offlineBuffSec'] = 0;
            } else {
                $data['offlineBuffSec'] -= $data['doubleBuffSec'];
            }
        }

        $earnGold += ($data['offlineBuffSec'] * $data['snailEarnPerSec']);

        // 发放奖励，更新buff时间
        if ($earnGold)
        {
            $update = ['gold' => $userBag['gold'] + $earnGold];

            $ret = $userBagModel->setUserBag($userId, $update);

            if (!$ret)
            {
                return 0;
            }

            $userBuffData = [];

            foreach($userBuff as $v)
            {
                $userBuffData[$v['buffType']] = $v['startTime'] . "_" . $v['endTime'] . "_" . $v['recTime'];
            }

            $key = $this->_getBuffKey();

            Redis::hset($key, $userId, json_encode($userBuffData));

            // 暂记此次获得的金币，用于分享双倍离线收益
            $this->setUserLastDouble($userId, $earnGold);
        }

        return $earnGold;
    }

    /**
     * 获取用户增益
     * @param string $userId
     * @return array
     */
    public function getUserBuff($userId = '')
    {
        if (!$userId) return array();

        $key = $this->_getBuffKey();

        if (!Redis::hexists($key, $userId))
        {
            return array();
        }

        $userBuffData = [];

        $userBuff = Redis::hget($key, $userId);

        $userBuff = json_decode($userBuff, true);

        foreach ($userBuff as $k => $v)
        {
            $buffStartEndTs = explode('_', $v);

            $userBuffData[] = [
                'buffType'  => $k,
                'startTime' => $buffStartEndTs[0],
                'endTime'   => $buffStartEndTs[1],
                'recTime'   => $buffStartEndTs[2],
            ];
        }

        return $userBuffData;
    }

    /**
     * 设置用户增益
     * @param string $userId
     * @param string $buffType
     * @param string $timeSec
     * @return bool
     */
    public function setUserBuff($userId = '', $buffType = '', $timeSec = '')
    {
        if (!$userId || !$buffType || !$timeSec) return false;

        $key = $this->_getBuffKey();

        $curTime = Carbon::now()->timestamp;

        $expTs = Carbon::now()->addSecond($timeSec)->timestamp;

        // 如果用户购买过buff
        if (Redis::hexists($key, $userId))
        {
            $userBuffJson = Redis::hget($key, $userId);

            $userBuff     = json_decode($userBuffJson, true);

            // 如果之前有购买过buff
            // buffType => 开始时间_结束时间_领取时间
            if (isset($userBuff[$buffType]))
            {

                $buffStartEndTs = explode('_', $userBuff[$buffType]);

                // 已过期
                if ($buffStartEndTs[1] <= $curTime)
                {
                    // 结束时间 大于 领取时间， 则还有未领取的奖励
                    if ($buffStartEndTs[1] > $buffStartEndTs[2])
                    {
                        // 把未领取的时间补足
                        $offsetSec = $buffStartEndTs[1] - $buffStartEndTs[2];

                        $userBuff[$buffType] = $curTime . '_' . ($expTs + $offsetSec) . '_' . $curTime;
                    } else {
                        $userBuff[$buffType] = $curTime . '_' . $expTs . '_' . $curTime;
                    }
                } else {
                    // 未过期
                    $buffStartEndTs[1] += $timeSec;
                    $userBuff[$buffType] = implode('_', $buffStartEndTs);
                }
            } else {
                $userBuff[$buffType] = $curTime . '_' . $expTs . '_' . $curTime;
            }

        } else {
            // 新增用户buff
            $userBuff[$buffType] = $curTime . '_' . $expTs . '_' . $curTime;
        }

        Redis::hset($key, $userId, json_encode($userBuff));

        return true;
    }

    /**
     * 获取用户今日分享得BUFF的次数
     * @param string $userId
     * @return bool|string
     */
    public function getUserShopShareNums($userId = '')
    {
        if (!$userId) return false;

        $key = $this->_getUserShopShareNumsKey($userId);

        if (!Redis::exists($key))
        {
            Redis::set($key, 0);

            Redis::expireat($key, Carbon::now()->endOfDay()->timestamp);
        }

        return Redis::get($key);
    }


    /**
     * 增加用户今日分享得BUFF的次数
     * @param string $userId
     * @return bool
     */
    public function incrUserShopShareNums($userId= '')
    {
        if (!$userId) return false;

        $key = $this->_getUserShopShareNumsKey($userId);

        Redis::incrby($key, 1);

        return true;
    }

    /**
     * 设置用户双倍值
     * @param string $userId
     * @param int $earnGold
     * @return bool
     */
    public function setUserLastDouble($userId = '', $earnGold = 0)
    {

        if (!$userId || !$earnGold) return false;

        $key =$this->_getUserDoubleBuffKey($userId);

        Redis::rpush($key, $earnGold);

        return true;
    }

    /**
     * 获取用户最近一次双倍值
     * @param string $userId
     * @return int
     */
    public function getUserLastDouble($userId = '')
    {

        if (!$userId) return 0;

        $key = $this->_getUserDoubleBuffKey($userId);

        if (!Redis::exists($key)) {

            Redis::rpush($key, 0);

            Redis::expireat($key, Carbon::now()->endOfDay()->timestamp);

            return 0;
        }

        return Redis::rpop($key);
    }

    /**
     * 增加用户今日分享领取双倍奖励的次数
     * @param string $userId
     * @param string $openGId
     * @return bool
     */
    public function incrUserDoubleNums($userId = '', $openGId = '')
    {
        if (!$userId || $openGId) return false;

        $key = $this->_getUserDoubleCheckKey($userId);

        Redis::hincrby($key, $openGId, 1);

        return true;
    }

    /**
     * 检测用户进入分享领取双倍奖励的状态
     * @param string $userId
     * @param string $openGId
     * @return bool
     */
    public function checkUserDoubleNums($userId = '', $openGId = '')
    {
        if (!$userId || !$openGId) return false;

        $key = $this->_getUserDoubleCheckKey($userId);

        if (Redis::exists($key)) {

            $data = Redis::hget($key, $openGId);

            if ($data >= 1) return false;
        } else {

            Redis::hset($key, $openGId, 0);

            Redis::expireat($key, Carbon::now()->endOfDay()->timestamp);
        }

        return true;
    }


    /**
     * 检测用户进入分享领取钻石的状态
     * @param string $userId
     * @param string $openGId
     * @return bool
     */
    public function checkUserDiamondNums($userId = '', $type = '')
    {
        if (!$userId || !$type) return false;

        $key = $this->_getUserDiamondCheckKey($userId);

        if (Redis::exists($key)) {

            $data = Redis::hget($key, $type);

            if ($data >= 1) return false;

        } else {

            Redis::hset($key, $type, 0);

        }

        return true;
    }

    /**
     * 增加用户今日分享领取钻石的次数
     * @param string $userId
     * @param string $openGId
     * @return bool
     */
    public function incrUserDiamondNums($userId = '', $type = '')
    {
        if (!$userId || !$type) return false;

        $key = $this->_getUserDiamondCheckKey($userId);

        Redis::hincrby($key, $type, 1);

        return true;
    }

    /**
     * 检测用户进入分享领取钻石的状态, 3小时一次
     * @param string $userId
     * @return bool
     */
    public function checkUserCycDiamondNums($userId = '')
    {

        if (!$userId) return false;

        $key = $this->_getUserCycDiamondCheckKey($userId);

        if (!Redis::exists($key)) {

            Redis::set($key, 0);

            Redis::expireat($key, Carbon::now()->addHour(3)->timestamp);

        }

        $data = Redis::get($key);

        $ret = $data >= 1 ? false : true;

        return $ret;
    }

    /**
     * 增加用户分享领取钻石的次数，3小时一次
     * @param string $userId
     * @return bool
     */
    public function incrUserCycDiamondNums($userId = '')
    {

        if (!$userId) return false;

        $key = $this->_getUserCycDiamondCheckKey($userId);

        Redis::incrby($key, 1);

        return true;
    }

    /**
     * 获取下一次可以分享领取钻石的时间
     * @param string $userId
     * @return bool|int
     */
    public function getUserCycDiamondTs($userId = '')
    {

        if (!$userId) return false;

        $key = $this->_getUserCycDiamondCheckKey($userId);

        if (!Redis::exists($key))
        {
            Redis::set($key, 0);

            Redis::expireat($key, Carbon::now()->addHour(3)->timestamp);
        }

        $ttl = Redis::ttl($key);

        $ttl = !$ttl ? 0 : $ttl;

        $ttl += Carbon::now()->timestamp;

        return $ttl;
    }

    private function _getBuffShopKey()
    {
        return 'CONFIG_BUFF_SHOP';
    }

    private function _getBuffKey()
    {
        return 'USER_BUFF';
    }

    private function _getUserShopShareNumsKey($userId = '')
    {
        return 'U_BUFF_SHARE_NUMS_' . $userId;
    }

    private function _getUserDoubleBuffKey($userId = '')
    {
        return 'U_BUFF_DOUBLE_' . $userId;
    }

    private function _getUserDoubleCheckKey($userId = '')
    {
        return 'U_BUFF_DOUBLE_CHECK_' . $userId;
    }

    private function _getUserDiamondCheckKey($userId = '')
    {
        return 'U_BUFF_DIAMOND_CHECK_' . $userId;
    }

    private function _getUserCycDiamondCheckKey($userId = '')
    {
        return 'U_BUFF_CYC_DIAMOND_CHECK_' . $userId;
    }

}
