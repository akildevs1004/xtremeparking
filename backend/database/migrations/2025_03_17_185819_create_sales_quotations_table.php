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
        Schema::create('sales_quotations', function (Blueprint $table) {

            $table->id();
            $table->integer("company_id");

            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("contact_number")->nullable();
            $table->string("whatsapp_number")->nullable();
            $table->string("email")->nullable();
            $table->string("address")->nullable();
            $table->string("device_type")->nullable();
            $table->integer("sensor_count")->nullable();
            $table->string("building_name")->nullable();
            $table->integer("building_type_id")->nullable();
            $table->datetime("created_datetime")->nullable();
            $table->integer("device_product_service_id")->nullable();
            $table->string("payment_type")->nullable();
            $table->decimal("discount", 8, 2)->default(0);
            $table->decimal("amount", 8, 2)->default(0);
            $table->integer("total_years")->default(0);



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
        Schema::dropIfExists('sales_quotations');
    }
};
