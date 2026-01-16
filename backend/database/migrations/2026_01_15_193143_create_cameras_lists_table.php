<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cameras_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('rtsp_url');
            $table->string('node_server_ip')->nullable();
            $table->timestamps();

            $table->index('node_server_ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cameras_lists');
    }
};
