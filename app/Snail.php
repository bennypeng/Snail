<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

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

        if (!Redis::exists($key)) {
            $snailConfigObj = Snail::where('id', '<=', 24)
                ->orderBy('id', 'asc')
                ->take(24)
                ->get();

            if (!$snailConfigObj) return array();

            $snailConfig = $snailConfigObj->toArray();

            foreach ($snailConfig as $k => &$v) {
                $v = json_encode($v);
            }

            Redis::hmset($key, $snailConfig);
        }

        unset($v);

        $snailConfig = Redis::hgetall($key);

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

        $userSnailGoldBuyNumsArr = $this->getUserSnailBuyNums($userId);

        $userSnailDiamondBuyNumsArr = $this->getUserSnailBuyNums($userId, 1);

        $maxLevel = $this->getUserSnailMaxLevel($userId);

        $maxLevel = !$maxLevel ? 1 : $maxLevel;

        if (!$maxLevel || !$userSnailGoldBuyNumsArr || !$userSnailDiamondBuyNumsArr || !$snailConf) return array();

        $snailList = [];

        foreach($snailConf as $k => $v)
        {

            // 最多可看的蜗牛
            if ($maxLevel <= 5)
            {
                if ($v['id'] >= $maxLevel + 5) continue;
            } else {
                if ($v['id'] >= $maxLevel + 3) continue;
            }

            // 不存在蜗牛
            if (!isset($userSnailGoldBuyNumsArr[$v['id']]) || !isset($userSnailDiamondBuyNumsArr[$v['id']]))
            {
                $costType     = 1;

                $costVal      = 999999;

                $unlockStatus = 0;
            }

            // 筛选购买的货币类型
            // 用金币买的
            if ($v['diamondPrice'] == -1 || $v['goldUnlockId'] <= $maxLevel)
            {
                // 对应的蜗牛购买了几次
                $userSnailBuyNums = $userSnailGoldBuyNumsArr[$v['id']];

                // 计算次数价格
                $numsPrice = $this->calcSnailPrice($v, $userSnailBuyNums);

                $costType     = 2;

                $costVal      = round($numsPrice);

                $unlockStatus = $v['goldUnlockId'] <= $maxLevel ? 1 : 0;

            } else {
                // 用钻石买的

                // 对应的蜗牛购买了几次
                $userSnailBuyNums = $userSnailDiamondBuyNumsArr[$v['id']];

                // 计算次数价格
                $numsPrice = $this->calcSnailDiamondPrice($v, $userSnailBuyNums);

                $costType     = 1;

                //$costVal      = $v['diamondPrice'];

                $costVal      = round($numsPrice);

                $unlockStatus = $v['diamondUnlockId'] <= $maxLevel && $v['diamondPrice'] != -1 ? 1 : 0;;
            }

            if ($v['id'] != 1)
            {
                if ($maxLevel < 6)
                {
                    // 可看剪影
                    if ($v['id'] <= $maxLevel)
                    {
                        $unlockStatus = 2;
                    }
                } else {
                    // 倒数一二只能看剪影
                    if ($maxLevel != 24 && $v['id'] > $maxLevel - 2 && $v['id'] <= $maxLevel)
                    {

                        $unlockStatus = 2;

                    } else if ($v['id'] > $maxLevel - 4 && $v['id'] <= $maxLevel - 2)
                    {
                        // 倒数三四只能用钻石买

                        if ($v['diamondUnlockId'] <= $maxLevel && $v['diamondPrice'] != -1)
                        {
                            $costType = 1;

                            $numsPrice = $this->calcSnailDiamondPrice($v, $userSnailBuyNums);

                            $costVal  = round($numsPrice);

                            //$costVal  = $v['diamondPrice'];

                            $unlockStatus = 1;

                        } else {
                            // 钻石未解锁， 只看剪影
                            $unlockStatus = 2;
                        }

                    } else if ($v['id'] == $maxLevel - 4 )
                    {
                        // 倒数第五个用视频购买

                        $costType = 3;

                        $costVal  = 0;

                        $unlockStatus = 1;

                    } else if ($maxLevel == 24 && $v['id'] > $maxLevel - 2 && $v['id'] <= $maxLevel)
                    {
                        // 当达到最大解锁等级，只能用钻石买

                        $costType = 1;

                        $costVal  = $v['diamondPrice'];

                        $unlockStatus = 1;
                    }
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

        // 先结算一次金币
        $userBagModel = new \App\UserBag;

        $userBagModel->settleGold($userId, true);

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

        $configModel = new \App\Config;

        $formulaStr = $configModel->getConfig('COST_GOLD_FORMULA');

        //  购买次数的价格公式// 计算次数价格
        $buyNumsFormula  = sprintf($formulaStr, $snailData['goldBase'], $snailData['goldFactor'], $snailData['goldPower'], $snailData['costGoldFactor'], $userSnailBuyNums);

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

        $configModel = new \App\Config;

        $formulaStr = $configModel->getConfig('BUY_GOLD_FORMULA');

        //  购买次数的价格公式// 计算次数价格
        $reclyFormula  = sprintf($formulaStr, $snailData['goldBase'], $snailData['goldFactor'], $snailData['goldPower']);

        $price = eval("return $reclyFormula;");

        $recyFactor = intval($configModel->getConfig('RECYCLE_FACTOR'));

        $recyFactor = !$recyFactor ? 1 : $recyFactor;

        $price = round($price * $recyFactor);

        return $price;
    }

    /**
     * 钻石价格计算
     * @param array $snailData
     * @param int $userSnailBuyNums
     * @return float|int
     */
    public function calcSnailDiamondPrice($snailData = [], $userSnailBuyNums = 1)
    {
        if (!$snailData) return 0;

        $price = $snailData['diamondPrice'] * pow(1.38, $userSnailBuyNums - 1);

        return $price;
    }

    /**
     * 获取用户购买蜗牛次数的集合
     * @param string $userId
     * @param int $type 1钻石2金币
     * @return array
     */
    public function getUserSnailBuyNums($userId = '', $type = 2)
    {
        if (!$userId) return array();

        $key = $type == 1 ? $this->_getUserSnailDiamondBuyNumsKey($userId) : $this->_getUserSnailGoldBuyNumsKey($userId);

        if (!Redis::exists($key))
        {
            Redis::hmset($key, array_fill(1,24,1));
        }

        return Redis::hgetall($key);
    }

    /**
     * 设置对应用户蜗牛的购买次数
     * @param string $userId
     * @param string $snailId
     * @param int $type 1钻石2金币
     * @return bool
     */
    public function setUserSnailBuyNums($userId= '', $snailId = '', $type = 2)
    {
        if (!$userId || !$snailId) return false;

        $key = $type == 1 ? $this->_getUserSnailDiamondBuyNumsKey($userId) : $this->_getUserSnailGoldBuyNumsKey($userId);

        return Redis::hincrby($key, $snailId, 1);

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

        if (!Redis::exists($key))
        {
            $userBagModel = new \App\UserBag;

            $userBag = $userBagModel->getUserBag($userId, true);

            $data = [];

            foreach($userBag['snailMap'] as $v) {
                $data[] = !$v ? 1 : $v[0];
            }

            rsort($data);

            Redis::set($key, $data[0]);
        }

        return Redis::get($key);
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

        Redis::incr($key);

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

        if (!Redis::exists($key))
        {
            Redis::set($key, 0);

            Redis::expireat($key, Carbon::now()->endOfDay()->timestamp);
        }

        return Redis::get($key);
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

        Redis::incrby($key, 1);

        return true;
    }

    private function _getSnailConfKey()
    {
        return 'CONFIG_SNAIL';
    }

    private function _getUserSnailGoldBuyNumsKey($userId = '')
    {
        return 'U_SNAIL_GOLD_NUMS_' . $userId;
    }

    private function _getUserSnailDiamondBuyNumsKey($userId = '')
    {
        return 'U_SNAIL_DIAMOND_NUMS_' . $userId;
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
