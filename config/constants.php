<?php

return [

    /**
     * 操作返回码相关
     */
    'NETWORK_ERROR'            => ['message' => '网络请求失败',                 'code' => 10000],
    'DATA_TYPE_ERROR'          => ['message' => '数据类型错误',                 'code' => 10001],
    'INTERNAL_ERROR'           => ['message' => '内部错误',                     'code' => 10002],
    'DECODE_ERROR'             => ['message' => '解密失败',                     'code' => 10003],
    'ARGS_ERROR'               => ['message' => '参数错误',                     'code' => 10004],
    'REG_ERROR'                => ['message' => '注册失败',                     'code' => 10005],
    'SIGNATURE_ERROR'          => ['message' => '签名错误',                     'code' => 10006],
    'OPENID_ERROR'             => ['message' => 'OPENID非法',                   'code' => 10007],
    'SESSIONID_EXP_ERROR'      => ['message' => 'SESSIONID已过期',              'code' => 10008],
    'CONF_ERROR'               => ['message' => '未找到相关配置',               'code' => 10009],
    'REPEAT_REWARD_ERROR'      => ['message' => '重复领取奖励',                 'code' => 10010],
    'USER_DATA_ERROR'          => ['message' => '未找到用户数据',               'code' => 10011],
    'DIAMOND_NOT_ENOUGH'       => ['message' => '钻石不足',                     'code' => 10012],
    'GOLD_NOT_ENOUGH'          => ['message' => '金币不足',                     'code' => 10013],
    'SEAT_FULL_ERROR'          => ['message' => '没有多余的位置',               'code' => 10014],
    'UNDEFINED_ERROR'          => ['message' => '未定义的类型',                 'code' => 10015],
    'MAX_VEDIO_NUM_ERROR'      => ['message' => '观看广告次数上限',             'code' => 10016],
    'UNLOCK_ERROR'             => ['message' => '未解锁',                       'code' => 10017],
    'DAILY_DAY_ERROR'          => ['message' => '前面有未领取的奖励',           'code' => 10018],
    'SNAIL_LEVEL_ERROR'        => ['message' => '蜗牛等级不一致',               'code' => 10019],
    'SEAT_ERROR'               => ['message' => '位置信息错误',                 'code' => 10020],
    'MAX_SHARE_NUM_ERROR'      => ['message' => '分享次数上限',                 'code' => 10021],
    'SNAIL_MAX_LEVEL_ERROR'    => ['message' => '已达到蜗牛最大合成等级',       'code' => 10022],
    'SNAIL_STATUS_ERROR'       => ['message' => '蜗牛状态不允许',               'code' => 10023],

    'SUCCESS'                  => ['message' => '操作成功',                     'code' => 10200],
    'FAILURE'                  => ['message' => '操作失败',                     'code' => 10300],

];
