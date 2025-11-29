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
        Schema::create('parking_members_vehicles_lists', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("member_id");
            $table->string("vehicle_number");
            $table->string("guest_first_name")->nullable();
            $table->string("guest_last_name")->nullable();
            $table->string("guest_address")->nullable();
            $table->string("guest_location")->nullable();
            $table->string("guest_company_details")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_members_vehicles_lists');
    }
};
