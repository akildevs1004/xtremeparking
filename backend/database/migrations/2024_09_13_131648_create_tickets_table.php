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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("customer_id")->nullable();
            $table->integer("operator_id")->nullable();
            $table->integer("status")->default(1);
            $table->string("subject");
            $table->string("description");
            $table->dateTime("created_datetime");
            $table->dateTime("updated_datetime")->nullable();


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
        Schema::dropIfExists('tickets');
    }
};
