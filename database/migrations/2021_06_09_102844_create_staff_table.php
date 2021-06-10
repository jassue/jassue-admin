<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->comment('员工姓名');
            $table->string('email', 40)->default('')->comment('员工邮箱')->index();
            $table->string('mobile', 16)->default('')->comment('员工手机')->index();
            $table->unsignedBigInteger('avatar_id')->default(0)->comment('员工头像id');
            $table->tinyInteger('gender')->default(0)->comment('员工性别 0未设置 1男 2女');
            $table->string('position', 40)->default('')->comment('职位');
            $table->string('job_number', 30)->default('')->comment('工号');
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
        Schema::dropIfExists('staff');
    }
}
