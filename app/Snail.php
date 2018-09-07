<?php

namespace App;

use \App\UserBag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Tests\Models\User;

class Snail extends Model
{
    protected $table      = 'snails';
    protected $primaryKey = 'id';
    public    $timestamps = false;
    //protected $dateFormat = 'U';

    /**
     * 获取蜗牛配置
     * @return array
     */
    public function getSnailConf()
    {
        $key = $this->_getSnailConfKey();

        if (!\Redis::exists($key)) {
            $snailConfigObj = Snail::where('id', '<=', 24)
                ->orderBy('id', 'asc')
                ->take(24)
                ->get();

            if (!$snailConfigObj) return array();

            $snailConfig = $snailConfigObj->toArray();

            foreach ($snailConfig as $k => &$v) {
                $v = json_encode($v);
            }

            \Redis::hmset($key, $snailConfig);
        }

        unset($v);

        $snailConfig = \Redis::hgetall($key);

        foreach($snailConfig as $k => &$v) {
            $v = json_decode($v, true);
        }

        unset($v);

        ksort($snailConfig);

        return $snailConfig;
    }

    /**
     * 获取蜗牛购买列表
     * @param string $userId
     * @return array
     */
    public function getUserSnailBuyList($userId = '')
    {
        if (!$userId) return array();

        $snailConf = $this->getSnailConf();

        $userSnailBuyNumsArr = $this->getUserSnailBuyNums($userId);

        $maxLevel = $this->getUserSnailMaxLevel($userId);

        $maxLevel = !$maxLevel ? 1 : $maxLevel;

        if (!$maxLevel || !$userSnailBuyNumsArr || !$snailConf) return array();

        $snailList = [];

        foreach($snailConf as $k => $v)
        {

            // 最多可看当前等级+3的蜗牛
            if ($v['id'] >= $maxLevel + 3) continue;

            $costType     = 1;

            $costVal      = $v['diamondPrice'];

            $unlockStatus = 0;

            if (!isset($userSnailBuyNumsArr[$v['id']]))
            {
                $costType     = 1;
                $costVal      = 999999;
                $unlockStatus = 0;
            } else {

                // 可以用钻石买的
                if ($maxLevel >= $v['diamondUnlockId'] && $v['diamondPrice'] != -1)
                {
                    $costType     = 1;
                    $costVal      = $v['diamondPrice'];
                    $unlockStatus = 1;
                }

                // 可以用金币买的
                if ($maxLevel >= $v['goldUnlockId'])
                {
                    // 对应的蜗牛购买了几次
                    $userSnailBuyNums = isset($userSnailBuyNumsArr[$v['id']]) ? $userSnailBuyNumsArr[$v['id']] : 1;

                    // 计算次数价格
                    $numsPrice = $this->calcSnailPrice($v, $userSnailBuyNums);

                    $costType     = 2;
                    $costVal      = round($numsPrice);
                    $unlockStatus = 1;
                }

                // 前两个显示为可见，但未解锁的
                if ($maxLevel > 2 && $v['id'] <= $maxLevel && $v['id'] > $maxLevel - 2)
                {
                    $unlockStatus = 2;
                }

                // 可以使用视频观看来获得蜗牛的
                if ($v['id'] == $maxLevel - 2)
                {

                    // 当天观看了几次视频

                    $costType     = 3;
                    $costVal      = 0;
                }

            }

            $snailList[$v['id']] = [
                $v['id'],
                $costType,
                $costVal,
                $unlockStatus,
            ];

            unset($costType, $costVal, $unlockStatus);
        }

        return $snailList;

    }

    /**
     * 计算蜗牛每秒可获得的金币数
     * @param array $snailData [[1, 1], [5, 1], [15, 1]]
     * @return int
     */
    public function calcSnailEarn($snailData = [])
    {
        if (!$snailData) return 0;

        $cycleEarnGold = 0;

        $snailConf = $this->getSnailConf();

        foreach($snailData as $v)
        {
            // 空
            if (!$v) continue;

            // 未上阵
            if ($v[1] != 1) continue;

            // 没有找到配置
            if (!isset($snailConf[$v[0] - 1])) continue;

            $snailDataConf = $snailConf[$v[0] - 1];

            $cycleEarnGold += floor($snailDataConf['cycleEarnGold'] / $snailDataConf['cycleMSec'] * 1000);
        }

        return round($cycleEarnGold);
    }


    /**
     * 计算蜗牛的价格
     * @param array $snailData
     * @param int $userSnailBuyNums
     * @return int
     */
    public function calcSnailPrice($snailData = [], $userSnailBuyNums = 1)
    {
        if (!$snailData) return 0;

        //  购买次数的价格公式// 计算次数价格
        $buyNumsFormula  = sprintf('%d * pow(%f, %d) * pow(%f, %d - 1)', $snailData['goldBase'], $snailData['goldFactor'], $snailData['goldPower'], $snailData['costGoldFactor'], $userSnailBuyNums);

        $price = eval("return $buyNumsFormula;");

        return $price;
    }

    /**
     * 计算蜗牛的回收价格
     * @param array $snailData
     * @return int|mixed
     */
    public function calcSnailReclyPrice($snailData = [])
    {
        if (!$snailData) return 0;

        //  购买次数的价格公式// 计算次数价格
        $reclyFormula  = sprintf('%d * pow(%f, %d)', $snailData['goldBase'], $snailData['goldFactor'], $snailData['goldPower']);

        $price = eval("return $reclyFormula;");

        $price = round($price * 0.94);

        return $price;
    }


    /**
     * 获取用户购买蜗牛次数的集合
     * @param string $userId
     * @return array
     */
    public function getUserSnailBuyNums($userId = '')
    {
        if (!$userId) return array();

        $key = $this->_getUserSnailBuyNumsKey($userId);

        if (!\Redis::exists($key))
        {
            \Redis::hmset($key, array_fill(1,24,1));
        }

        return \Redis::hgetall($key);
    }

    /**
     * 设置对应用户蜗牛的购买次数
     * @param string $userId
     * @param string $snailId
     * @return int
     */
    public function setUserSnailBuyNums($userId= '', $snailId = '')
    {
        if (!$userId || !$snailId) return false;

        $key = $this->_getUserSnailBuyNumsKey($userId);

        \Redis::hincrby($key, $snailId, 1);

        return true;
    }

    /**
     * 获取用户蜗牛的最大等级
     * @param string $userId
     * @return bool|int|string
     */
    public function getUserSnailMaxLevel($userId = '')
    {
        if (!$userId) return 1;

        $key = $this->_getUserMaxSnailKey($userId);

        if (!\Redis::exists($key))
        {
            $userBagModel = new UserBag;

            $userBag = $userBagModel->getUserBag($userId, true);

            $data = [];

            foreach($userBag['snailMap'] as $v) {
                $data[] = !$v ? 0 : $v[0];
            }

            rsort($data);

            \Redis::set($key, $data[0]);
        }

        return \Redis::get($key);
    }

    /**
     * 增加用户蜗牛最大等级
     * @param string $userId
     * @return bool|int
     */
    public function incrUserSnailMaxLevel($userId = '')
    {
        if (!$userId) return 0;

        $key = $this->_getUserMaxSnailKey($userId);

        \Redis::incr($key);

        return true;
    }

    /**
     * 获取用户今日观看视频得蜗牛的次数
     * @param string $userId
     * @return bool|string
     */
    public function getUserSnailVedioNums($userId = '')
    {
        if (!$userId) return false;

        $key = $this->_getUserSnailVedioNumsKey($userId);

        if (!\Redis::exists($key))
        {
            \Redis::set($key, 0);

            \Redis::expireat($key, Carbon::now()->endOfDay()->timestamp);
        }

        return \Redis::get($key);
    }


    /**
     * 增加用户今日观看视频得蜗牛的次数
     * @param string $userId
     * @return bool
     */
    public function incrUserSnailVedioNums($userId= '')
    {
        if (!$userId) return false;

        $key = $this->_getUserSnailVedioNumsKey($userId);

        \Redis::incrby($key, 1);

        return true;
    }

    private function _getSnailConfKey()
    {
        return 'CONFIG_SNAIL';
    }

    private function _getUserSnailBuyNumsKey($userId = '')
    {
        return 'U_SNAIL_NUMS_' . $userId;
    }

    private function _getUserMaxSnailKey($userId = '')
    {
        return 'U_SNAIL_MAX_' . $userId;
    }

    private function _getUserSnailVedioNumsKey($userId = '')
    {
        return 'U_SNAIL_VEDIO_NUMS_' . $userId;
    }


}
