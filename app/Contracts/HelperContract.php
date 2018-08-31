<?php

namespace App\Contracts;

use Carbon\Carbon;

interface HelperContract
{
    public function getRandomByWeight(array $weightValuesArr);                          //  通过权重获取随机数
    public function parseNum2Bit(int $number, int $minLen = 6);                         //  十进制转二进制，并按指定格式输出
    public function parseNums2Bool(array $numbers);                                     //  包含0|1的数组转换成布尔数组输出
    public function parseBools2Nums(array $bools);                                      //  包含true|false的数组转换成数组输出
    public function generateDateRange(Carbon $start_date, Carbon $end_date);            //  获取指定范围内的所有时间戳
    public function getNextFifteenTs();                                                 //  获取下一个15分钟的时间戳
}