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
        Schema::create('parking_blocked_logs', function (Blueprint $table) {
            $table->id();

            // relations
            $table->integer('company_id');
            $table->integer('parking_member_id')->nullable();
            $table->dateTime('created_datetime')->nullable();

            // common fields
            $table->string('plate_number')->nullable();
            $table->string('action')->nullable(); // blocked / allowed
            $table->string('reason')->nullable();

            // ===== RAW DUMP FIELDS =====
            $table->string('raw_device_no')->nullable();
            $table->dateTime('raw_capture_time')->nullable();
            $table->string('raw_plate_no')->nullable();
            $table->string('raw_vehicle_color')->nullable();
            $table->string('raw_vehicle_type')->nullable();
            $table->string('raw_vehicle_brand')->nullable();
            $table->string('raw_moving_direction')->nullable();
            $table->string('raw_validity')->nullable();
            $table->string('raw_country_region')->nullable();
            $table->string('raw_plate_color')->nullable();
            $table->string('raw_plate_size')->nullable();
            $table->string('raw_plate_type')->nullable();
            $table->string('raw_province')->nullable();
            $table->string('raw_camera_no')->nullable();

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
        Schema::dropIfExists('parking_blocked_logs');
    }
};
