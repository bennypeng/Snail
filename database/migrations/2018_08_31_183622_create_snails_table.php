<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSnailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('snails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('level')->unsigned()->default(1)->comment('等级');
            $table->integer('resIdx')->unsigned()->comment('资源索引');
            $table->integer('goldUnlockId')->unsigned()->default(0)->comment('金币解锁ID');
            $table->integer('diamondUnlockId')->unsigned()->default(0)->comment('钻石解锁ID');
            $table->integer('goldBase')->unsigned()->default(0)->comment('金币基数');
            $table->float('goldFactor')->unsigned()->default(0)->comment('金币因子');
            $table->integer('goldPower')->unsigned()->default(0)->comment('金币幂值');
            $table->float('costGoldFactor')->unsigned()->default(0)->comment('金币花费因子');
            $table->bigInteger('cycleEarnGold')->unsigned()->default(0)->comment('每圈收入的金币');
            $table->integer('cycleMSec')->unsigned()->default(0)->comment('每圈花费的毫秒数');
            $table->integer('diamondPrice')->default(-1)->comment('钻石价格, -1不可购买');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('snails');
    }
}
