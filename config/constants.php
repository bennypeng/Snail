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

    'SUCCESS'                  => ['message' => '操作成功',                     'code' => 10200],
    'FAILURE'                  => ['message' => '操作失败',                     'code' => 10300],

];
