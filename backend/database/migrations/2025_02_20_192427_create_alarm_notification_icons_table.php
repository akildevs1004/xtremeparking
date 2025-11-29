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
        Schema::create('alarm_notification_icons', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id")->nullable();
            $table->string("notification_type");
            $table->string("image")->nullable();
            $table->string("google_map_image")->nullable();


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
        Schema::dropIfExists('alarm_notification_icons');
    }
};
