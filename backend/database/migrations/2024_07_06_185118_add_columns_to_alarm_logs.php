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
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->renameColumn('device_id', 'serial_number');
            $table->renameColumn('status', 'alarm_status');
            $table->string('alarm_type');
            $table->integer('customer_id')->nullable();
            $table->integer('time_duration_seconds')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alarm_logs', function (Blueprint $table) {
            $table->renameColumn('serial_number', 'device_id');
            $table->renameColumn('alarm_status', 'status');
            $table->dropColumn('alarm_type');
            $table->dropColumn('customer_id');
            $table->dropColumn('time_duration_seconds');
        });
    }
};
