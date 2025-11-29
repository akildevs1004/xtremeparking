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
        Schema::table('device_zones', function (Blueprint $table) {
            $table->string('alarm_status')->after('alarm_end_datetime')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_zones', function (Blueprint $table) {
            $table->dropColumn("alarm_status");
        });
    }
};
