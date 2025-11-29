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
        Schema::create('sales_inquiries', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("business_source_id");
            $table->string("business_source_info");
            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("contact_number")->nullable();
            $table->string("whatsapp_number")->nullable();
            $table->string("email")->nullable();
            $table->string("address")->nullable();
            $table->string("device_type")->nullable();
            $table->string("sensor_count")->nullable();
            $table->string("building_name")->nullable();
            $table->integer("building_type_id")->nullable();
            $table->datetime("created_datetime")->nullable();



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
        Schema::dropIfExists('sales_inquiries');
    }
};
