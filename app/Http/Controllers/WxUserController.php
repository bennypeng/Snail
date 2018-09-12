<?php

namespace App\Http\Controllers;

use App\WxUser;
use App\UserBag;
use App\ShopBuff;
use App\Snail;
use WXBizDataCrypt;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class WxUserController extends Controller
{
    protected $wxUserModel;
    protected $userBagModel;
    protected $shopModel;
    protected $snailModel;
    protected $configModel;

    public function __construct()
    {
        $this->wxUserModel  = new WxUser;
        $this->userBagModel = new UserBag;
        $this->shopModel    = new ShopBuff;
        $this->snailModel   = new Snail;
        $this->configModel  = new \App\Config;
    }

    /**
     * 用户登录
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login(Request $req)
    {

        $jsCode        = $req->get('js_code', '');
        $encryptedData = $req->get('encryptedData', '');
        $iv            = $req->get('iv', '');
        $sessionId     = $req->header('sessionId', '');
        $signature     = $req->get('signature', '');
        $rawData       = $req->get('rawData', '');

        // 获取session_key 和 openId
        $sessionData   = $this->_getSessionData($jsCode);

        Log::info('sessionId：' . $sessionId . ', sessionData：', $sessionData);

        // 网络请求失败
        if (!$sessionData)
        {
            return response()->json(Config::get('constants.NETWORK_ERROR'));
        }

        // 数据类型错误
        if (!is_array($sessionData))
        {
            return response()->json(Config::get('constants.DATA_TYPE_ERROR'));
        }

        // 内部错误
        if (isset($sessionData['errcode']) && $sessionData['errcode'])
        {
            Log::error('/api/user/login', $sessionData);
            return response()->json(Config::get('constants.INTERNAL_ERROR'));
        }

        $openId     = $sessionData['openid'];
        $sessionKey = $sessionData['session_key'];

        // 数据签名校验
        if ($rawData && !$sessionId && $signature != sha1($rawData . $sessionKey))
        {
            return response()->json(Config::get('constants.SIGNATURE_ERROR'));
        }

        // 判断sessionId的合法性
        if ($sessionId && $sessionId != 'undefined')
        {
            $key = $this->wxUserModel->getUserSessionIdKey($sessionId);

            if (Redis::exists($key))
            {
                if (Redis::hget($key, 'openId') != $openId)
                {
                    // openId不一致，需要重新登陆
                    return response()->json(Config::get('constants.OPENID_ERROR'));
                }
            } else {
                // sessionId已过期
                return response()->json(Config::get('constants.SESSIONID_EXP_ERROR'));
            }
        } else {

            $userData = $this->wxUserModel->getUserByOpenId($openId);

            if (!$userData)
            {
                // 如果找不到该openId， 则进行注册
                $pc = new WXBizDataCrypt(env('APPID'), $sessionKey);
                $errCode = $pc->decryptData($encryptedData, $iv, $data );
                if ($errCode == 0)
                {
                    $dataArr = json_decode($data, true);

                    $update = [
                        'openId'    => $openId,
                        'cId'       => '',
                        'gender'    => $dataArr['gender'],
                        'gender'    => $dataArr['gender'],
                        'avatarUrl' => $dataArr['avatarUrl'],
                        'language'  => $dataArr['language'],
                        'nickName'  => $dataArr['nickName'],
                        'country'   => $dataArr['country'],
                        'province'  => $dataArr['province'],
                        'city'      => $dataArr['city'],
                    ];

                    $userId = $this->wxUserModel->registerUser($update);
                    // 注册成功， 则生成sessionId返回给客户端
                    if ($userId)
                    {
                        $sessionId = $this->_3rd_session(16);
                        $key       = $this->wxUserModel->getUserSessionIdKey($sessionId);
                        $this->wxUserModel->setUserSessionId($key, [
                            'openId'      => $openId,
                            'session_key' => $sessionKey,
                            'userId'      => $userId
                        ]);

                        // 初始化背包
                        $this->userBagModel->initBags($userId);

                        Log::info('创建用户成功，userId:' . $userId);

                    } else {
                        // 注册失败
                        Log::error('注册失败，update: ', $update);

                        return response()->json(Config::get('constants.REG_ERROR'));
                    }
                } else {
                    // 解密失败
                    return response()->json(Config::get('constants.DECODE_ERROR'));
                }
            } else {
                // 如果能找到openId， 则进行生成sessionId
                $sessionId = $this->_3rd_session(16);
                $key       = $this->wxUserModel->getUserSessionIdKey($sessionId);
                $this->wxUserModel->setUserSessionId($key, [
                    'openId'      => $openId,
                    'session_key' => $sessionKey,
                    'userId'      => $userData['id']
                ]);
            }
        }

        $userId          = $this->wxUserModel->getUserIdBySessionId($sessionId);
        $offlineGold     = $this->shopModel->settleBuffOfflineGold($userId);
        $userBags        = $this->userBagModel->getUserBag($userId, true);
        $userBuff        = $this->shopModel->getUserBuff($userId);
        $snailEarnPerSec = $this->snailModel->calcSnailEarn($userBags['snailMap']);
        $adConfig        = $this->configModel->getConfig('AUDIT_SWITCH');

        $adConfig = json_decode($adConfig);

        $userBags['snailMap'] = array_values($userBags['snailMap']);

        // 设置离线半小时收益buff
        if (!$userBuff)
        {
            $this->shopModel->setUserBuff($userId, 2, 1800);

            Log::info('用户增加半小时离线收益1，userId：' . $userId);
        } else {
            foreach($userBuff as $v)
            {
                if ($v['buffType'] != 2) continue;

                if ($v['endTime'] > time()) continue;

                $this->shopModel->setUserBuff($userId, 2, 1800);

                Log::info('用户增加半小时离线收益2，userId：' . $userId);
            }
        }

        // 设置登录时间
        $this->wxUserModel->setUserOpTs($userId, time());

        // 计算第一只蜗牛需要消耗的钱
        $buyNum    = $this->snailModel->getUserSnailBuyNums($userId);
        $snailConf = $this->snailModel->getSnailConf();
        $refPrice  = round($this->snailModel->calcSnailPrice($snailConf[0], $buyNum[1]));

        return response()->json(
            array_merge(
                array(
                    'sessionId'       => $sessionId,
                    'refPrice'        => $refPrice,
                    'offlineGold'     => $offlineGold,
                    'snailEarnPerSec' => $snailEarnPerSec,
                    'userBags'        => $userBags,
                    'userBuff'        => $userBuff,
                    'adConfig'        => $adConfig
                ),
                Config::get('constants.SUCCESS')
            )
        );
    }

    /**
     * 通过js_code获取 session_key 和 openid
     * @param string $jsCode
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function _getSessionData($jsCode = '') {

        $client = new Client;
        $resp = $client->request('GET', 'https://api.weixin.qq.com/sns/jscode2session', [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8'
            ],
            'query' => [
                'appid' => env('APPID'),
                'secret' => env('APPSECRET'),
                'js_code' => $jsCode,
                'grant_type' => 'authorization_code'
            ]
        ]);

        if ($resp->getStatusCode() == 200) {
            $resArr = json_decode($resp->getBody(), true);
            return $resArr;
        }

        return false;
    }

    /**
     * 生成3rd_session
     * @param $len
     * @return bool|string
     */
    private function _3rd_session($len)
    {
        $result = '';
        $fp = @fopen('/dev/urandom', 'rb');

        if ($fp !== FALSE)
        {
            $result .= @fread($fp, $len);
            @fclose($fp);
        } else {
            Log::error('Can not open /dev/urandom.');
            return false;
        }

        $result = strtr(base64_encode($result), '+/', '-_');

        return substr($result, 0, $len);
    }
}
