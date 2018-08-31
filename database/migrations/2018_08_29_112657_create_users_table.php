<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->comment('用户自增ID');
            $table->string('openId')->comment('开放ID')->unique();
            $table->string('cId', 30)->nullable()->comment('渠道ID');
            $table->char('gender', 1)->nullable()->comment('性别');
            $table->string('avatarUrl', 300)->nullable()->comment('头像地址');
            $table->string('language', 30)->nullable()->comment('语言');
            $table->string('nickName', 100)->nullable()->comment('昵称');
            $table->string('country', 50)->nullable()->comment('国家');
            $table->string('province', 100)->nullable()->comment('省份');
            $table->string('city', 50)->nullable()->comment('城市');
            $table->timestamps();
            $table->index('openId');
            $table->index('cId');
        });

        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE users AUTO_INCREMENT=10000"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
