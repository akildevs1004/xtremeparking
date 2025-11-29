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
        Schema::table('device_notifications_managers', function (Blueprint $table) {
            $table->renameColumn("device_id", "serial_number");
            $table->renameColumn("device_zone_id", "zone_name");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_notifications_managers', function (Blueprint $table) {
            $table->dropColumn("serial_number");
            $table->dropColumn("zone_name");
        });
    }
};
