<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDingtalkUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dingtalk_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable()->comment('foreign key for users table');
            $table->string('userid', 32)->unique()->comment('dingtalk用户id，类似于openid');
            $table->string('name')->nullable();
            $table->string('remark')->nullable();
            $table->string('mobile', 32)->nullable();
            $table->string('avatar')->nullable();
            $table->string('position')->nullable()->comment('dingtalk用户职位信息');
            $table->string('unionid', 32)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dingtalk_users');
    }
}
