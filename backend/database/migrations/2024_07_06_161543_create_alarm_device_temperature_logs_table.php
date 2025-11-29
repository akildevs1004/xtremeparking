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
        Schema::create('alarm_device_temperature_logs', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id")->nullable();
            $table->integer("branch_id")->nullable();
            $table->integer("serial_number");
            $table->dateTime("log_time");
            $table->decimal("temparature")->default(0);
            $table->decimal("humidity")->default(0);








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
        Schema::dropIfExists('alarm_device_temperature_logs');
    }
};
