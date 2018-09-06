<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('day')->unsigned()->default(1)->comment('第几天');
            $table->integer('diamond')->unsigned()->default(0)->comment('钻石数');
            $table->integer('gold')->unsigned()->default(0)->comment('金币数');
            $table->integer('snailIds')->unsigned()->nullable()->comment('赠送的蜗牛ID');
            $table->unique('day');
            $table->index('day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_rewards');
    }
}
