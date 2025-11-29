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
        Schema::table('parking_camera_logs', function (Blueprint $table) {
            $table->integer('duration_in_hours')->nullable()->after('duration_in_minutes');
            $table->integer('duration_per_hour_amount')->nullable()->after('duration_in_hours');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parking_camera_logs', function (Blueprint $table) {
            $table->dropColumn('duration_in_hours');
            $table->dropColumn('duration_per_hour_amount');
        });
    }
};
