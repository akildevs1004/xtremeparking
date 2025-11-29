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
        Schema::create('alarm_events', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("branch_id")->nullable();
            $table->integer("customer_id")->nullable();
            $table->string("serial_number");
            $table->string("zone")->nullable();
            $table->string("area")->nullable();
            $table->datetime("alarm_start_datetime")->nullable();
            $table->datetime("alarm_end_datetime")->nullable();
            $table->integer("alarm_status")->default(0);
            $table->string("alarm_type")->default(0);
            $table->integer("resolved_time_seconds")->default(0);
            $table->integer("alarm_end_manually")->default(0);
            $table->integer("alarm_category")->nullable();


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
        Schema::dropIfExists('alram_events');
    }
};
