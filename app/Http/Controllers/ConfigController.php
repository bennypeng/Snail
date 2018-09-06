<?php

namespace App\Http\Controllers;

use App\UserBag;
use App\DailyReward;
use App\ShopBuff;
use App\Snail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

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

        $userDailyNums = $this->dailyModel->getUserDailyNums($userId);

        // 参数错误，只能按顺序签到
        if ($userDailyNums + 1 != $day)
        {
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $dailyReward = $userDailyReward[$day];

        // 重复领取奖励
        if ($dailyReward['status'] != 0)
        {
            return response()->json(Config::get('constants.REPEAT_REWARD_ERROR'));
        }

        $userBag = $this->userBagModel->getUserBag($userId);

        // 无法获得用户信息
        if (!$userBag)
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
        $diamond = $userBag['diamond'] + $dailyReward['diamond'];
        $ret = $this->userBagModel->setUserBag($userId, ['diamond' => $diamond]);

        // 发放奖励失败，回退状态
        if (!$ret)
        {
            Log::error('每日签到奖励领取失败，用户ID：' . $userId);
            $dailyReward['status'] = 0;
            $this->dailyModel->setUserDailyReward($userId, $day, $dailyReward);
            return response()->json(Config::get('constants.FAILURE'));
        }

        return response()->json(
            array_merge(
                ['diamond' => $diamond, 'gold' => $userBag['gold']],
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

        // 参数错误
        if (!$goodId)
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
            Log::error('购买商品失败，用户ID：' . $userId);
            /**
             * 退款操作
             * @todo
             */
            return response()->json(Config::get('constants.FAILURE'));
        }

        // 增加增益效果
        $this->shopModel->setUserBuff($userId, $buffConf['buffType'], $buffConf['timeSec']);

        return response()->json(Config::get('constants.SUCCESS'));
    }
}
