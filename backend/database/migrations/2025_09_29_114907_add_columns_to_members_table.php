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
        Schema::table('parking_members', function (Blueprint $table) {

            $table->string("vehicle_country_region")->nullable();
            $table->string("vehicle_type")->nullable();
            $table->string("vehicle_plate_type")->nullable();
            $table->string("vehicle_plate_color")->nullable();
            $table->string("vehicle_brand")->nullable();
            $table->string("vehicle_color")->nullable();

            $table->string("blocked_reason")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parking_members', function (Blueprint $table) {

            $table->dropColumn("vehicle_country_region");
            $table->dropColumn("vehicle_type");
            $table->dropColumn("vehicle_plate_type");
            $table->dropColumn("vehicle_plate_color");
            $table->dropColumn("vehicle_brand");
            $table->dropColumn("vehicle_color");

            $table->dropColumn("blocked_reason");
        });
    }
};
