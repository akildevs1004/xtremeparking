<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parking_camera_logs', function (Blueprint $table) {
            // // Standard index for vehicle plate lookups
            // $table->index('log_vehicle_number');

            // // Standard index for time-based filtering/sorting
            // $table->index('out_time');

            // OPTIONAL: A composite index if you frequently query "Which car exited at this time?"
            $table->index(['log_vehicle_number', 'out_time'], 'idx_vehicle_out_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_camera_logs', function (Blueprint $table) {
            $table->dropIndex(['idx_vehicle_out_time']);
        });
    }
};