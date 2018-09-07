<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersBagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_bags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userId')->unsigned()->comment('用户ID');
            $table->string('gold', 100)->default('0')->comment('金币数量');
            $table->integer('diamond')->unsigned()->default(0)->comment('钻石数量');
            //$table->integer('snailNums')->unsigned()->default(0)->comment('蜗牛数量');
            $table->string('item_1', 30)->default('[]');
            $table->string('item_2', 30)->default('[]');
            $table->string('item_3', 30)->default('[]');
            $table->string('item_4', 30)->default('[]');
            $table->string('item_5', 30)->default('[]');
            $table->string('item_6', 30)->default('[]');
            $table->string('item_7', 30)->default('[]');
            $table->string('item_8', 30)->default('[]');
            $table->string('item_9', 30)->default('[]');
            $table->string('item_10', 30)->default('[]');
            $table->string('item_11', 30)->default('[]');
            $table->string('item_12', 30)->default('[]');
            $table->string('item_13', 30)->default('[]');
            $table->string('item_14', 30)->default('[]');
            $table->string('item_15', 30)->default('[]');
            $table->index('userId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_snails');
    }
}
