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
        Schema::create('customer_alarm_events', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('branch_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('device_id');
            $table->integer('device_zone_id')->nullable();
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->integer('response_minutes')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('customer_alarm_events');
    }
};
