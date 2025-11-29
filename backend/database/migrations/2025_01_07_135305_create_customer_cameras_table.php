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
        Schema::create('customer_cameras', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("branch_id");
            $table->integer("customer_id");
            $table->string("title");
            $table->string("camera_url");
            $table->integer("display_order")->nullable();



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
        Schema::dropIfExists('customer_cameras');
    }
};
