<?php

namespace App\Http\Controllers;

use App\UserBag;
use App\DailyReward;
use App\ShopBuff;
use App\Snail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ConfigController extends Controller
{
    protected $configModel;
    protected $dailyModel;
    protected $shopModel;
    protected $userBagModel;
    protected $snailModel;

    public function __construct()
    {
        $this->configModel  = new \App\Config();
        $this->dailyModel   = new DailyReward;
        $this->shopModel    = new ShopBuff;
        $this->userBagModel = new UserBag;
        $this->snailModel   = new Snail;
    }

    /**
     * 每日签到奖励列表
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailyConf(Request $req)
    {
        $userId = $req->get('userId', '');

        $userDailyReward = $this->dailyModel->getUserDailyReward($userId);

        return response()->json(
            array_merge(
                ['dailyAward' => array_values($userDailyReward)],
                Config::get('constants.SUCCESS')
            )
        );

    }

    /**
     * 领取每日奖励
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDailyAward(Request $req)
    {

        $day = $req->route('day');
        $userId = $req->get('userId', '');

        // 参数错误
        if (!$day)
        {
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $userDailyReward = $this->dailyModel->getUserDailyReward($userId);

        // 未找到相关配置
        if (!isset($userDailyReward[$day]))
        {
            return response()->json(Config::get('constants.CONF_ERROR'));
        }

        $userDailyStatus = $this->dailyModel->getUserDailyStatus($userId);

        // 今天已经签到过了，重复领取奖励
        if ($userDailyStatus == 1)
        {
            return response()->json(Config::get('constants.REPEAT_REWARD_ERROR'));
        }

        // 是否连续
        foreach ($userDailyReward as $v)
        {
            if ($v['day'] > $day) continue;

            if ($v['status'] == 0 && $v['day'] != $day)
            {
                return response()->json(Config::get('constants.DAILY_DAY_ERROR'));
            }
        }

        $dailyReward = $userDailyReward[$day];

        // 重复领取奖励
        if ($dailyReward['status'] != 0)
        {
            return response()->json(Config::get('constants.REPEAT_REWARD_ERROR'));
        }

        $userBags = $this->userBagModel->getUserBag($userId, true);

        // 无法获得用户信息
        if (!$userBags)
        {
            return response()->json(Config::get('constants.USER_DATA_ERROR'));
        }

        $dailyReward['status'] = 1;

        // 操作失败
        if (!$this->dailyModel->setUserDailyReward($userId, $day, $dailyReward))
        {
            return response()->json(Config::get('constants.FAILURE'));
        }

        // 发放奖励
        $userBags['diamond'] += $dailyReward['diamond'];
        $ret = $this->userBagModel->setUserBag($userId, ['diamond' => $userBags['diamond']]);

        // 增加领奖次数
        $this->dailyModel->setUserDailyStatus($userId);

        // 发放奖励失败，回退状态
        if (!$ret)
        {
            //Log::error('每日签到奖励领取失败，用户ID：' . $userId);
            //$dailyReward['status'] = 0;
            //$this->dailyModel->setUserDailyReward($userId, $day, $dailyReward);
            return response()->json(Config::get('constants.FAILURE'));
        }

        $userBags['snailMap'] = array_values($userBags['snailMap']);

        return response()->json(
            array_merge(
                ['userBags' => $userBags],
                Config::get('constants.SUCCESS')
            )
        );
    }

    /**
     * 增益商店配置
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBuffShopConf(Request $req)
    {
        $buffShopConf = $this->shopModel->getBuffShopConf();

        return response()->json(
            array_merge(
                ['buffShop' => array_values($buffShopConf)],
                Config::get('constants.SUCCESS')
            )
        );
    }

    /**
     * 购买增益
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyBuff(Request $req)
    {

        $goodId = $req->route('goodId');

        $userId = $req->get('userId', '');

        $t      = $req->get('t', '');

        // 参数错误
        if (!$goodId || !$t)
        {
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $buffShopConf = $this->shopModel->getBuffShopConf();

        // 未找到相关配置
        if (!isset($buffShopConf[$goodId - 1]))
        {
            return response()->json(Config::get('constants.CONF_ERROR'));
        }

        $buffConf = $buffShopConf[$goodId - 1];

        // 检测今日分享次数
        if ($t == 2 && $goodId == 1)
        {
            $shareNums = $this->shopModel->getUserShopShareNums($userId);

            // 分享次数上限
            if ($shareNums >= 5)
            {
                return response()->json(Config::get('constants.MAX_SHARE_NUM_ERROR'));
            }

            // 增加增益效果
            $this->shopModel->setUserBuff($userId, $buffConf['buffType'], $buffConf['timeSec']);

            // 增加分享次数
            $this->shopModel->incrUserShopShareNums($userId);
        } else {

            $userBag  = $this->userBagModel->getUserBag($userId);

            // 无法获得用户信息
            if (!$userBag)
            {
                return response()->json(Config::get('constants.USER_DATA_ERROR'));
            }

            // 钻石不足
            if ($buffConf['costVal'] > $userBag['diamond'])
            {
                return response()->json(Config::get('constants.DIAMOND_NOT_ENOUGH'));
            }

            // 扣除相应钻石
            $diamond = $userBag['diamond'] - $buffConf['costVal'];
            $ret = $this->userBagModel->setUserBag($userId, ['diamond' => $diamond]);

            // 购买失败，人工处理退款
            if (!$ret)
            {
                /**
                 * 退款操作
                 * @todo
                 */
                return response()->json(Config::get('constants.FAILURE'));
            }

            // 增加增益效果
            $this->shopModel->setUserBuff($userId, $buffConf['buffType'], $buffConf['timeSec']);
        }

        return response()->json(
            array_merge(
                array(
                    'userBuff' => $this->shopModel->getUserBuff($userId),
                ),
                Config::get('constants.SUCCESS')
            )
        );
    }

}
