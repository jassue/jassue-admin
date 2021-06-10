<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id')->comment('员工id')->index();
            $table->string('name', 30)->comment('用户名称');
            $table->string('mobile', 16)->comment('手机号码')->index();
            $table->string('password', 80)->default('')->comment('密码');
            $table->tinyInteger('status')->default(0)->comment('0=启用 1=禁用');
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
        Schema::dropIfExists('admins');
    }
}
