<?php

namespace App\Http\Controllers;

use App\Services\HelperService;
use App\UserBag;
use App\Snail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SnailController extends Controller
{
    protected $userBagModel;
    protected $snailModel;

    public function __construct()
    {
        $this->userBagModel = new UserBag;
        $this->snailModel   = new Snail;
    }


    /**
     * 蜗牛购买列表
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $req)
    {
        $userId = $req->get('userId', '');

        $snailList = $this->snailModel->getUserSnailBuyList($userId);

        return response()->json(
            array_merge(
                ['list' => array_values($snailList)],
                Config::get('constants.SUCCESS')
            )
        );
    }

    /**
     * 合成蜗牛
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function combine(Request $req)
    {
        $userId = $req->get('userId', '');

        $from   = $req->get('from', '');

        $to     = $req->get('to', '');

        // 参数错误
        if (!$from || !$to)
        {
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $userBags = $this->userBagModel->getUserBag($userId, true);

        // 只是更换位置
        if (
            (
            (isset($userBags['snailMap'][$from][0]) && !isset($userBags['snailMap'][$to][0]))
            || (isset($userBags['snailMap'][$from][0]) && isset($userBags['snailMap'][$to][0]) && $userBags['snailMap'][$from][0] != $userBags['snailMap'][$to][0])
            ) && $to <= 15 && $to >= 1)
        {

            // 蜗牛上阵中
            if ($userBags['snailMap'][$from][1] == 1 || (isset($userBags['snailMap'][$to][1]) && $userBags['snailMap'][$to][1] == 1))
            {
                return response()->json(Config::get('constants.SNAIL_STATUS_ERROR'));
            }

            // 更新蜗牛数据
            $temp = $userBags['snailMap'][$to];

            $userBags['snailMap'][$to] = $userBags['snailMap'][$from];

            $userBags['snailMap'][$from] = $temp;

            $update = [
                'item_' . $from => isset($temp[0]) ? '[' . $temp[0] . ', ' . $temp[1] .']' : '[]',
                'item_' . $to   => '[' . $userBags['snailMap'][$to][0] . ', ' . $userBags['snailMap'][$to][1] .']'
            ];

            Log::info('交换位置，userId: ' . $userId . ', data: ', $update);

            // 操作失败
            if (!$this->userBagModel->setUserBag($userId, $update))
            {
                return response()->json(Config::get('constants.FAILURE'));
            }

            $fromSeat = !isset($userBags['snailMap'][$from][0]) ? [0, 0] : $userBags['snailMap'][$from];

            return response()->json(
                array_merge(
                    ['userBags' => $userBags, 'changeSeats' => [$from => $fromSeat, $to => $userBags['snailMap'][$to]]],
                    Config::get('constants.SUCCESS')
                )
            );


        }

        // 没有找到位置信息
        if (!isset($userBags['snailMap'][$from][0]) || !isset($userBags['snailMap'][$to][0]))
        {
            return response()->json(Config::get('constants.SEAT_ERROR'));
        }

        // 合成的蜗牛信息不一致
        if ($userBags['snailMap'][$from][0] != $userBags['snailMap'][$to][0])
        {
            return response()->json(Config::get('constants.SNAIL_LEVEL_ERROR'));
        }

        // 禁止合成超过24级的蜗牛
        if ($userBags['snailMap'][$to][0] + 1 > 24)
        {
            return response()->json(Config::get('constants.SNAIL_MAX_LEVEL_ERROR'));
        }

        // 获取用户当前解锁的最大等级
        $maxLevel = $this->snailModel->getUserSnailMaxLevel($userId);

        // 如果超过用户当前解锁的最大等级
        if ($userBags['snailMap'][$from][0] + 1 > $maxLevel)
        {
            $this->snailModel->incrUserSnailMaxLevel($userId);
        }

        // 更新蜗牛数据
        $userBags['snailMap'][$from] = [];

        $userBags['snailMap'][$to][0] += 1;

        $userBags['snailMap'][$to][1] = 0;

        $update = [
            'item_' . $from => '[]',
            'item_' . $to   => '[' . $userBags['snailMap'][$to][0] . ', ' . $userBags['snailMap'][$to][1] .']'
        ];

        Log::info('合成蜗牛，userId: ' . $userId . ', data: ', $update);

        // 操作失败
        if (!$this->userBagModel->setUserBag($userId, $update))
        {
            return response()->json(Config::get('constants.FAILURE'));
        }

        $seatArr = $userBags['snailMap'][$to];

        $userBags['snailMap'] = array_values($userBags['snailMap']);

        return response()->json(
            array_merge(
                ['userBags' => $userBags, 'changeSeats' => [$from => [0, 0], $to => $seatArr]],
                Config::get('constants.SUCCESS')
            )
        );

    }

    /**
     * 购买蜗牛
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function buy(Request $req)
    {
        $userId  = $req->get('userId', '');

        $snailId = $req->get('snailId', '');

        // 参数错误
        if (!$snailId)
        {
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $userBags = $this->userBagModel->getUserBag($userId, true);

        $userSnail = $userBags['snailMap'];

        // 获得可插入的槽位
        $idx = array_search([], $userSnail);

        // 槽位已满
        //if (count(array_filter($userSnail)) >= 15)
        if (!$idx)
        {
            return response()->json(Config::get('constants.SEAT_FULL_ERROR'));
        }

        $snailList = $this->snailModel->getUserSnailBuyList($userId);

        // 未找到相关配置
        if (!isset($snailList[$snailId]))
        {
            return response()->json(Config::get('constants.CONF_ERROR'));
        }

        $snailConf = $snailList[$snailId];

        // 未解锁
        if ($snailConf[3] != 1)
        {
            return response()->json(Config::get('constants.UNLOCK_ERROR'));
        }

        // 消耗类型的检测
        if ($snailConf[1] == 1)
        {
            // 钻石不足
            if ($userBags['diamond'] < $snailConf[2])
            {
                return response()->json(Config::get('constants.DIAMOND_NOT_ENOUGH'));
            }

            // 增加蜗牛的购买次数
            $buyNum = $this->snailModel->setUserSnailBuyNums($userId, $snailId, 1);

            // 扣除钻石
            $userBags['diamond'] -= $snailConf[2];
            $update = [
                'diamond' => $userBags['diamond'],
                'item_' . $idx => '[' . $snailId . ', 0]'
            ];

        } else if ($snailConf[1] == 2)
        {
            // 金币不足
            if ($userBags['gold'] < $snailConf[2])
            {
                return response()->json(Config::get('constants.GOLD_NOT_ENOUGH'));
            }

            // 增加蜗牛的购买次数
            $buyNum = $this->snailModel->setUserSnailBuyNums($userId, $snailId);

            // 扣除金币
            $userBags['gold'] -= $snailConf[2];
            $update = [
                'gold' => $userBags['gold'],
                'item_' . $idx => '[' . $snailId . ', 0]'
            ];

        } else if ($snailConf[1] == 3)
        {
            // 今日观看视频的次数
            $vNums = $this->snailModel->getUserSnailVedioNums($userId);

            // 观看次数上限
            if ($vNums >= 6)
            {
                return response()->json(Config::get('constants.MAX_VEDIO_NUM_ERROR'));
            }

            // 增加观看次数
            $this->snailModel->incrUserSnailVedioNums($userId);

            $update = [
                'item_' . $idx => '[' . $snailId . ', 0]'
            ];

        } else {
            // 未定义的类型
            return response()->json(Config::get('constants.UNDEFINED_ERROR'));
        }

        if (isset($update) && $update)
        {
            Log::info('购买蜗牛，userId: ' . $userId . ', data: ', $update);

            // 操作失败
            if (!$this->userBagModel->setUserBag($userId, $update))
            {
                return response()->json(Config::get('constants.FAILURE'));
            }

            // 刷新背包内容

            $userBags['snailMap'][$idx] = [intval($snailId), 0];

            $userBags['snailMap'] = array_values($userBags['snailMap']);

            // 计算价格
            if (isset($buyNum))
            {
                $snailConf = $this->snailModel->getSnailConf();

                $refPrice = $snailConf[1] == 2
                    ? round($this->snailModel->calcSnailPrice($snailConf[$snailId - 1], $buyNum))
                    : round($this->snailModel->calcSnailDiamondPrice($snailConf[$snailId - 1], $buyNum));

                //$refPrice  = round($this->snailModel->calcSnailPrice($snailConf[$snailId - 1], $buyNum));
            } else {
                $refPrice = $snailConf[2];
            }

        } else {
            $refPrice = $snailConf[2];
        }

        return response()->json(
            array_merge(
                [
                    'userBags' => $userBags,
                    'refPrice' => $refPrice,
                    'changeSeats' => [$idx => [intval($snailId), 0]]
                ],
                Config::get('constants.SUCCESS')
            )
        );
    }

    /**
     * 上/下阵
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function join(Request $req)
    {
        $userId = $req->get('userId', '');

        $seatId = $req->get('seatId', '');

        $t      = $req->get('t', '');

        // 参数错误
        if (!$seatId || !$t)
        {
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $userBags = $this->userBagModel->getUserBag($userId, true);

        // 没有找到位置信息
        if (!isset($userBags['snailMap'][$seatId]))
        {
            return response()->json(Config::get('constants.SEAT_ERROR'));
        }

        // 设置上/下阵
        if (isset($userBags['snailMap'][$seatId][1]))
        {
            // 上阵要检测已有多少个在跑
            if ($t == 1)
            {
                $counts = 0;

                foreach($userBags['snailMap'] as $v)
                {
                    if (!isset($v[1])) continue;

                    if ($v[1] == 1)
                    {
                        $counts++;
                    }
                }

                // 超出上阵数量
                if ($counts >= 10)
                {
                    return response()->json(Config::get('constants.SEAT_FULL_ERROR'));
                }
            }

            $upStatus = $t == 1 ? 1 : 0;

            // 状态不一样才更新
            if ($upStatus != $userBags['snailMap'][$seatId][1])
            {
                $userBags['snailMap'][$seatId][1] = $userBags['snailMap'][$seatId][1] == 1 ? 0 : 1;

                list($snailId, $joinStatus) = $userBags['snailMap'][$seatId];

                $update = [
                    'item_' . $seatId => '[' . $snailId . ', ' . $joinStatus .']'
                ];

                Log::info('上/下蜗牛，userId: ' . $userId . ', data: ', $update);

                // 操作失败
                if (!$this->userBagModel->setUserBag($userId, $update))
                {
                    return response()->json(Config::get('constants.FAILURE'));
                }
            }

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
     * 回收蜗牛
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function recly(Request $req)
    {
        $userId = $req->get('userId', '');

        $seatId = $req->get('seatId', '');

        // 参数错误
        if (!$seatId)
        {
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $userBags = $this->userBagModel->getUserBag($userId, true);

        // 没有找到位置信息
        if (!isset($userBags['snailMap'][$seatId][0]))
        {
            return response()->json(Config::get('constants.SEAT_ERROR'));
        }

        $snailId = $userBags['snailMap'][$seatId][0];

        $snailConf = $this->snailModel->getSnailConf();

        // 配置信息错误
        if (!isset($snailConf[$snailId - 1]) || $snailConf[$snailId - 1]['id'] != $snailId)
        {
            return response()->json(Config::get('constants.CONF_ERROR'));
        }

        $reclyPrice = $this->snailModel->calcSnailReclyPrice($snailConf[$snailId - 1]);

        $userBags['gold'] += $reclyPrice;

        $userBags['snailMap'][$seatId] = [];

        $update = [
            'gold' => $userBags['gold'],
            'item_' . $seatId => '[]'
        ];

        Log::info('回收蜗牛，userId: ' . $userId . ', data: ', $update);

        // 操作失败
        if (!$this->userBagModel->setUserBag($userId, $update))
        {
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

    public function pack(Request $req)
    {
        $userId = $req->get('userId', '');

        $seatId = $req->get('seatId', '');

        // 参数错误
        if (!$seatId)
        {
            return response()->json(Config::get('constants.ARGS_ERROR'));
        }

        $userBags = $this->userBagModel->getUserBag($userId, true);

        // 位置不空，位置信息错误
        if ($userBags['snailMap'][$seatId])
        {
            return response()->json(Config::get('constants.SEAT_ERROR'));
        }

        // 临时写的，当前等级可生成集装箱蜗牛
        $confArr = [
            1  => [1 => 100],
            2  => [1 => 100],
            3  => [1 => 100],
            4  => [1 => 80, 2 => 20],
            5  => [1 => 80, 2 => 20],
            6  => [1 => 50, 2 => 40, 3 => 10],
            7  => [1 => 40, 2 => 40, 3 => 20],
            8  => [1 => 30, 2 => 40, 3 => 20, 4 => 10],
            9  => [1 => 20, 2 => 40, 3 => 20, 4 => 20],
            10 => [1 => 20, 2 => 20, 3 => 20, 4 => 30, 5 => 10],
            11 => [2 => 30, 3 => 30, 4 => 20, 5 => 20],
            12 => [2 => 20, 3 => 20, 4 => 20, 5 => 30, 6 => 10],
            13 => [3 => 30, 4 => 30, 5 => 20, 6 => 20],
            14 => [3 => 20, 4 => 20, 5 => 20, 6 => 30, 7 => 10],
            15 => [4 => 30, 5 => 30, 6 => 20, 7 => 20],
            16 => [4 => 20, 5 => 20, 6 => 20, 7 => 30, 8 => 10],
            17 => [5 => 30, 6 => 30, 7 => 20, 8 => 20],
            18 => [5 => 20, 6 => 20, 7 => 20, 8 => 30, 9 => 10],
            19 => [6 => 30, 7 => 30, 8 => 20, 9 => 20],
            20 => [6 => 20, 7 => 30, 8 => 30, 9 => 10, 10 => 10],
            21 => [7 => 30, 8 => 30, 9 => 20, 10 => 20],
            22 => [7 => 40, 8 => 30, 9 => 10, 10 => 10, 11 => 10],
            23 => [8 => 30, 9 => 30, 10 => 20, 11 => 20],
            24 => [8 => 40, 9 => 30, 10 => 10, 11 => 10, 12 => 10],
        ];

        $maxLevel = $this->snailModel->getUserSnailMaxLevel($userId);

        // 没有找到概率配置
        if (!isset($confArr[$maxLevel]))
        {
            return response()->json(Config::get('constants.CONF_ERROR'));
        }

        $helper = new HelperService();

        $snailId = $helper->getRandomByWeight($confArr[$maxLevel]);

        $update = [
            'item_' . $seatId => '[' . $snailId . ', 0]'
        ];

        // 操作失败
        if (!$this->userBagModel->setUserBag($userId, $update))
        {
            return response()->json(Config::get('constants.FAILURE'));
        }

        Log::info('集装箱增加蜗牛，userId: ' . $userId . ', data: ', $update);

        return response()->json(
            array_merge(
                [
                    'changeSeats' => [$seatId => [intval($snailId), 0]]
                ],
                Config::get('constants.SUCCESS')
            )
        );
    }

}
