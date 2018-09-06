<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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

        if (!\Redis::exists($key)) {
            $snailConfigObj = Snail::where('id', '<=', 24)
                ->orderBy('id', 'asc')
                ->take(24)
                ->get();

            if (!$snailConfigObj) return array();

            $snailConfig = $snailConfigObj->toArray();

            foreach ($snailConfig as $k => &$v) {
                $v = json_encode($v);
            }

            \Redis::hmset($key, $snailConfig);
        }

        unset($v);

        $snailConfig = \Redis::hgetall($key);

        foreach($snailConfig as $k => &$v) {
            $v = json_decode($v, true);
        }

        unset($v);

        return $snailConfig;
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

        return $cycleEarnGold;
    }

    private function _getSnailConfKey()
    {
        return 'CONFIG_SNAIL';
    }


}
