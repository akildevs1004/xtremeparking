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
        Schema::table('parking_members_vehicles_lists', function (Blueprint $table) {
            $table->string("parking_slot")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parking_members_vehicles_lists', function (Blueprint $table) {
            $table->dropColumn("parking_slot");
        });
    }
};
