<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ShopBuffsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("
            INSERT INTO shop_buffs (`name`, costVal, costType, buffType, timeSec, `desc`) VALUES
            ('加速X2', 30, 1, 1, 1800, '蜗牛速度X2持续30分钟'),
            ('加速X2', 60, 1, 1, 3600, '蜗牛速度X2持续60分钟'),
            ('加速X2', 180, 1, 1, 14400, '蜗牛速度X2持续240分钟'),
            ('离线收入', 100, 1, 2, 21600, '离线收入持续6小时'),
            ('离线收入', 360, 1, 2, 86400, '离线收入持续24小时'),
            ('离线收入', 2400, 1, 2, 604800, '离线收入持续7天')
        ");
    }
}
