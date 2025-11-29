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
        Schema::create('device_zones', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('branch_id')->nullable();
            $table->integer('device_id');
            $table->integer('alarm_type_id')->nullable(); //Burglary/Medical/temp/wwater
            $table->string('sensor_name')->nullable();
            $table->string('wired')->nullable();
            $table->string('location')->nullable();
            $table->string('area_code')->nullable();
            $table->string('zone_code')->nullable();
            $table->dateTime('alarm_start_datetime')->nullable();
            $table->dateTime('alarm_end_datetime')->nullable();
            $table->string('delay')->nullable();
            $table->string('hours24')->nullable();



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
        Schema::dropIfExists('device_zones');
    }
};
