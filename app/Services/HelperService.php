<?php

namespace App\Services;

use App\Contracts\HelperContract;
use Carbon\Carbon;

class HelperService implements HelperContract
{
    /**
     * 通过权重获取随机数
     * @param array $weightValuesArr
     * @return int|string
     */
    public function getRandomByWeight(array $weightValuesArr) {
        $rand = mt_rand(1, (int) array_sum($weightValuesArr));

        foreach ($weightValuesArr as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) {
                return $key;
            }
        }
    }

    /**
     * 十进制转二进制，并按指定格式输出
     * @param int $number   十进制数
     * @param int $minLen  输出的最小长度
     * @return array
     */
    public function parseNum2Bit(int $number, int $minLen = 6) {
        $binData = decbin($number);
        $binLen  = strlen($binData);
        if ($binLen < $minLen)
            $binData = sprintf('%0' . $minLen . 's', $binData);
        return str_split($binData);
    }

    /**
     * 转换成包含布尔型的数组输出
     * @param array $numbers    包含0|1的数组
     * @return array
     */
    public function parseNums2Bool(array $numbers) {
        foreach ($numbers as &$v) {
            $v = $v == 1 ? true : false;
        }
        return $numbers;
    }

    /**
     * 转换成包含0|1的数组输出
     * @param array $bools    包含true|false的数组
     * @return array
     */
    public function parseBools2Nums(array $bools) {
        foreach ($bools as &$v) {
            $v = $v ? 1 : 0;
        }
        return $bools;
    }

    /**
     * 获取指定范围内的所有时间戳
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    public function generateDateRange(Carbon $start_date, Carbon $end_date) {
        $dates = [];
        for($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->timestamp;
        }
        return $dates;
    }

    /**
     * 获取下一个15分钟的时间戳
     * @return int
     */
    public function getNextFifteenTs() {
        $tObj = Carbon::now();
        $tObj->second = 0;
        $curMinute = $tObj->minute;
        if ($curMinute >= 0 && $curMinute < 15) {
            $m = 15;
        } else if ($curMinute >= 15 && $curMinute < 30) {
            $m = 30;
        } else if ($curMinute >= 30 && $curMinute < 45) {
            $m = 45;
        } else {
            $tObj->hour += 1;
            $m = 0;
        }
        $tObj->minute = $m;
        return $tObj->timestamp;
    }

}