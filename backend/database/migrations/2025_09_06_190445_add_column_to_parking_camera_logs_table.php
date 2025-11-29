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
            $table->dateTime('manual_gate_opened_at')->nullable()->after('payment_status');
            $table->dateTime('automatic_gate_opened_at')->nullable()->after('manual_gate_opened_at');
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
            $table->dropColumn('manual_gate_opened_at');
            $table->dropColumn('automatic_gate_opened_at');
        });
    }
};
