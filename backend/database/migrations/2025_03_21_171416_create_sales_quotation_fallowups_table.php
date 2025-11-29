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
        Schema::create('sales_quotation_fallowups', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("quotation_id");
            $table->string("status");
            $table->string("message");
            $table->dateTime("created_datetime");
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
        Schema::dropIfExists('sales_quotation_fallowups');
    }
};
