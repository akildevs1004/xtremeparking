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
        Schema::create('ticket_sensor_tests', function (Blueprint $table) {
            $table->id();
            $table->integer("ticket_id");

            $table->integer("company_id");
            $table->integer("customer_id");
            $table->string("serial_number");
            $table->integer("device_id");
            $table->string("zone_code")->nullable();
            $table->string("area_code")->nullable();
            $table->dateTime("test_datetime")->nullable();
            $table->integer("test_status")->default(0);
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
        Schema::dropIfExists('ticket_sensor_tests');
    }
};
