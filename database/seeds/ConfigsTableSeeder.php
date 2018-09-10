<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("
            INSERT INTO configs (`key`, `value`, `desc`) VALUES
            ('INIT_GOLD', 100, '初始金币'),
            ('INIT_DIAMOND', 0, '初始钻石'),
            ('RECYCLE_FACTOR', 0.94, '回收价格系数'),
            ('OFFLINE_GAIN_TIME', 1800, '离线累计产出时间（秒）'),
            ('FULL_BUFF_FACTOR', 0.1, '满蜗牛额外加成系数'),
            ('BUY_GOLD_FORMULA', '%d * pow(%f, %d)', '初始金币公式，初始价格 = goldBase * goldFactor^goldPower'),
            ('COST_GOLD_FORMULA', '%d * pow(%f, %d) * pow(%f, %d - 1)', '消耗金币公式：消耗金币 = 初始价格 * goldFactor^goldPower * costGoldFactor^(当前购买次数 - 1)'),
            ('AUDIT_SWITCH', '{\"SPEED_SHARE\": 1,\"UNLOCK_SHARE\": 1,\"GAME_SHARE\": 1,\"DOUBLE_SHARE\": 1}', '审核开关')
        ");
    }
}
