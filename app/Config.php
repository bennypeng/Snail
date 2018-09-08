<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Config extends Model
{
    protected $table      = 'configs';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    /**
     * 获取配置
     * @return array
     */
    public function getConfig($item = '')
    {
        $key = $this->_configKey();

        if (!Redis::exists($key)) {

            $arr = [];

            $configObj = Config::all();

            if (!$configObj) return array();

            $config = $configObj->toArray();

            foreach ($config as $k => $v) {
                $arr[$v['key']] = $v['value'];
            }

            Redis::hmset($key, $arr);
        }

        $config = Redis::hgetall($key);

        if ($item)
        {
            if (isset($config[$item]))
            {
                return $config[$item];
            }
        }

        return $config;
    }

    private function _configKey()
    {
        return 'CONFIG';
    }

}
