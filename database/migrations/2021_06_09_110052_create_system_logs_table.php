<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->comment('操作者id')->index();
            $table->string('operator_name', 24)->comment('操作者姓名');
            $table->text('content')->comment('操作内容');
            $table->string('client_ip', 24)->comment('客户端ip');
            $table->string('user_agent')->comment('用户代理标识');
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
        Schema::dropIfExists('system_logs');
    }
}
