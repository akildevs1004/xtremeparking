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
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->string('floor_no');
            $table->integer('slot_number');
            $table->string('status')->default('available'); // available, occupied, reserved, maintenance
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['company_id', 'floor_no']);
            $table->index(['company_id', 'floor_no', 'slot_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parking_slots');
    }
};
