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
        Schema::create('parking_camera_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->nullable();
            $table->integer('device_id');
            $table->string('log_timestamp');
            $table->string('log_vehicle_number');
            $table->string('device_in_out');

            $table->string('in_background_file_name')->nullable();
            $table->string('out_background_file_name')->nullable();


            $table->dateTime('in_time')->nullable();
            $table->dateTime('out_time')->nullable();
            $table->integer('duration_in_minutes')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_mode')->nullable();

            $table->integer('membership_id')->nullable();
            $table->integer('cancel_status')->default(0);
            $table->string('cancel_reason')->nullable();

            $table->string('raw_device_no')->nullable();
            $table->string('raw_capture_time')->nullable();
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
            $table->text('raw_info')->nullable();

            $table->string('raw_event_category')->nullable();
            $table->string('raw_event_type')->nullable();
            $table->string('raw_camera_code')->nullable();
            $table->string('raw_direction')->nullable();
            $table->string('raw_lane')->nullable();

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
        Schema::dropIfExists('parking_camera_logs');
    }
};
