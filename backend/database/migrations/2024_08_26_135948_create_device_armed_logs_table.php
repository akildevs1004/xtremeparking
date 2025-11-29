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
        Schema::create('device_armed_logs', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id")->nullable();
            $table->string("serial_number")->nullable();
            $table->datetime("armed_datetime")->nullable();
            $table->datetime("disarm_datetime")->nullable();
            $table->integer("duration_in_minutes")->nullable();

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
        Schema::dropIfExists('device_armed_logs');
    }
};
