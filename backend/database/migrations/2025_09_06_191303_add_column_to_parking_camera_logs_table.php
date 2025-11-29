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
            $table->dateTime('acknowledged_from_device_at')->nullable()->after('automatic_gate_opened_at');
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
            $table->dropColumn('acknowledged_from_device_at');
        });
    }
};
