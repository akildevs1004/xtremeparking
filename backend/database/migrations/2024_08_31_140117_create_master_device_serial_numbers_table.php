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
        Schema::create('master_device_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id")->nullable();
            $table->string("master_serial_number");
            $table->string("device_model");
            $table->string("device_type");
            $table->string("device_description")->nullable();
            $table->dateTime("assigned_datetime")->nullable();
            $table->string("picture")->nullable();


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
        Schema::dropIfExists('master_device_serial_numbers');
    }
};
