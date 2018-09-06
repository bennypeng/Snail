<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DailyRewardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("
            INSERT INTO daily_rewards (`day`, diamond, gold) VALUES
            (1, 100, 0),
            (2, 120, 0),
            (3, 150, 0),
            (4, 150, 0),
            (5, 180, 0),
            (6, 180, 0),
            (7, 200, 0)
        ");
    }
}
