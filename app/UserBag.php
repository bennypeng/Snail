<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class UserBag extends Model
{
    protected $table = 'users_bags';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * 初始化背包
     * @param string $userId
     * @return bool
     */
    public function initBags($userId = '')
    {
        if (!$userId) return false;

        $data = [
            'userId' => $userId,
            'gold' => 100,
            'diamond' => 0,
            'item_1' => '[1, 0]',
            'item_2' => '[]', 'item_3' => '[]', 'item_4' => '[]', 'item_5' => '[]',
            'item_6' => '[]', 'item_7' => '[]', 'item_8' => '[]', 'item_9' => '[]',
            'item_10' => '[]', 'item_11' => '[]', 'item_12' => '[]',
            'item_13' => '[]', 'item_14' => '[]', 'item_15' => '[]'
        ];

        $bagId = UserBag::insertGetId($data);

        if (!$bagId) return false;

        $data['id'] = $bagId;

        $key = $this->_getUserBagKey($userId);
        Redis::hmset($key, $data);

        return true;
    }

    /**
     * 获取用户背包
     * @param string $userId
     * @param bool $parseItem
     * @return array|bool
     */
    public function getUserBag($userId = '', $parseItem = false)
    {
        if (!$userId) return array();

        $key = $this->_getUserBagKey($userId);

        if (!Redis::exists($key)) {
            $userBag = UserBag::where('userId', $userId)->first();

            if (!$userBag) return array();

            Redis::hmset($key, $userBag->toArray());

        } else {
            $userBag = Redis::hgetall($key);
        }

        $userBag['snailMap'] = [];

        // 解析蜗牛数据
        if ($parseItem) {

            $snailData = [];

            foreach ($userBag as $k => $v) {
                if (strstr($k, 'item')) {
                    $idxArr = explode('_', $k);

                    $snailData[$idxArr[1]] = json_decode($v, true);

                    unset($userBag[$k]);
                }
            }

            ksort($snailData);

            $userBag['snailMap'] = $snailData;

        }

        unset($userBag['id']);

        $userBag['gold'] = intval($userBag['gold']);

        return $userBag;
    }

    /**
     * 设置背包数据
     * @param string $userId
     * @param array $data
     * @return bool
     */
    public function setUserBag($userId = '', $update = [])
    {
        if (!$userId || !$update) return false;

        // 消费前，计算当前的产出
        if (isset($update['gold']))
        {
            $update['gold'] += $this->settleGold($userId, false);
        }

        if (!UserBag::where('userId', $userId)->update($update)) return false;

        $key = $this->_getUserBagKey($userId);

        // 不删除KEY来缓解数据库压力
        //Redis::del($key);
        Redis::hmset($key, $update);

        return true;
    }

    /**
     * 更新金币
     * @param string $userId
     * @param bool $update 更新到缓存，是否更新金币，不更新直接返回挣了多少金币
     * @return bool|float|int
     */
    public function settleGold($userId = '', $update = false)
    {
        if (!$userId) return false;

        $userModel = new \App\WxUser;

        $userOpTs = $userModel->getUserOpTs($userId);

        $earnGold = 0;

        if ($userOpTs) {

            $tsDiff = time() - $userOpTs;

            if ($tsDiff && $tsDiff > 0) {

                $snailModel = new \App\Snail;

                $userBags = $this->getUserBag($userId, true);

                $snailEarnPerSec = $snailModel->calcSnailEarn($userBags['snailMap']);

                $earnGold = round($snailEarnPerSec * $tsDiff);

                $userModel->setUserOpTs($userId, time());

                // 更新到缓存
                if ($update)
                {
                    $key = $this->_getUserBagKey($userId);

                    $gold = Redis::hget($key, 'gold');

                    Redis::hset($key, 'gold', $gold + $earnGold);

                }

                Log::info('结算产出金币，userId：' . $userId . ', earnGold：' . $earnGold);
            }

        }

        return $earnGold;
    }

    private function _getUserBagKey($userId = '')
    {
        return 'U_BAG_' . $userId;
    }
}
