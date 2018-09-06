<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopBuffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_buffs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->comment('名称');
            $table->integer('costVal')->default(0)->comment('消耗货币值');
            $table->integer('costType')->default(1)->comment('消耗货币类型');
            $table->tinyInteger('buffType')->default(1)->comment('增益类型');
            $table->integer('timeSec')->default(0)->comment('持续秒数');
            $table->string('desc', 100)->nullable()->comment('备注');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_buffs');
    }
}
