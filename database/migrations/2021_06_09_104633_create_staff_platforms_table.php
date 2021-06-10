<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffPlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id')->comment('员工id')->index();
            $table->tinyInteger('type')->comment('平台类型 1:企业微信 2:微信');
            $table->string('open_id', 60)->default('')->comment('平台id');
            $table->string('union_id', 60)->default('')->comment('平台联合id');
            $table->string('token', 60)->default('')->comment('平台access_token');
            $table->string('nickname', 60)->default('')->comment('昵称');
            $table->string('avatar')->default('')->comment('头像');
            $table->index(['type', 'open_id']);
            $table->index(['type', 'union_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_platforms');
    }
}
