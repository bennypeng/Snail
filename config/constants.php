<?php

return [

    /**
     * 操作返回码相关
     */
    'NETWORK_ERROR'            => ['message' => '网络请求失败',                 'code' => 10000],
    'DATA_TYPE_ERROR'          => ['message' => '数据类型错误',                 'code' => 10001],
    'INTERNAL_ERROR'           => ['message' => '内部错误',                     'code' => 10002],
    'DECODE_ERROR'             => ['message' => '内部错误',                     'code' => 10003],
    'ARGS_ERROR'               => ['message' => '参数错误',                     'code' => 10004],
    'REG_ERROR'                => ['message' => '注册失败',                     'code' => 10005],
    'SIGNATURE_ERROR'          => ['message' => '签名错误',                     'code' => 10006],
    'OPENID_ERROR'             => ['message' => 'OPENID非法',                   'code' => 10007],
    'SESSIONID_EXP_ERROR'      => ['message' => 'SESSIONID已过期',              'code' => 10008],

    //'LOGIN_SUCCESS'            => ['message' => '登录成功',                     'code' => 10100],
    //'LOGIN_ERROR'              => ['message' => '登录失败',                     'code' => 10101],
    //'LOGIN_OUT'                => ['message' => '登出成功',                     'code' => 10102],
    //'LOGIN_OUT_ERROR'          => ['message' => '登出失败',                     'code' => 10103],

    'SUCCESS'                  => ['message' => '操作成功',                     'code' => 10200],

];
