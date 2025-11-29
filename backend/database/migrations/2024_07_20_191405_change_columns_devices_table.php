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
        Schema::table('devices', function (Blueprint $table) {

            $table->renameColumn('temparature_alarm_status', 'temperature_alarm_status');
            $table->renameColumn('temparature_alarm_start_datetime', 'temperature_alarm_start_datetime');
            $table->renameColumn('temparature_alarm_end_datetime', 'temperature_alarm_end_datetime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
