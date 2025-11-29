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
        Schema::create('ticket_responses', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("ticket_id");
            $table->integer("customer_id")->nullable();
            $table->integer("operator_id")->nullable();
            $table->integer("technician_id")->nullable();
            $table->string("description")->nullable();
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
        Schema::dropIfExists('ticket_responses');
    }
};
