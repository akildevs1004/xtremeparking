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
        Schema::create('customer_alarm_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('alarm_id');
            $table->integer('company_id');
            $table->integer('branch_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('device_id');
            $table->string('title');
            $table->text('notes');
            $table->dateTime('created_datetime');


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
        Schema::dropIfExists('customer_alarm_notes');
    }
};
