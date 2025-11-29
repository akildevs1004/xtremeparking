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
        Schema::create('alarm_events_technicians', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("branch_id")->nullable();
            $table->integer("customer_id");
            $table->string("serial_number");
            $table->string("zone")->nullable();
            $table->string("area")->nullable();
            $table->dateTime("alarm_start_datetime");
            $table->dateTime("alarm_end_datetime")->nullable();
            $table->integer("alarm_status");
            $table->string("alarm_type")->nullable();
            $table->integer("response_minutes")->nullable();
            $table->integer("alarm_end_manually")->nullable();
            $table->integer("alarm_category")->nullable();
            $table->string("pin_verified_by")->nullable();
            $table->integer("sensor_zone_id")->nullable();
            $table->string("alarm_source")->nullable();
            $table->boolean("forwarded")->nullable();
            $table->string("operator_name")->nullable();
            $table->string("operator_email")->nullable();
            $table->string("operator_id")->nullable();
            $table->string("security_name")->nullable();
            $table->string("security_email")->nullable();
            $table->integer("security_id")->nullable();
            $table->integer("pin_verified_by_id")->nullable();
            $table->string("created_event_from")->nullable();
            $table->integer("technician_id")->nullable();
            $table->integer("ticket_id")->nullable();





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
        Schema::dropIfExists('alarm_events_technicians');
    }
};
