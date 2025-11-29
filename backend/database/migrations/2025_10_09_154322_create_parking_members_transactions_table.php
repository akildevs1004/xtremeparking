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
        Schema::create('parking_members_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("member_id");
            $table->decimal("credit", 8, 2);
            $table->decimal("debit", 8, 2);
            $table->string("notes");
            $table->integer("parking_log_id");
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
        Schema::dropIfExists('parking_members_transactions');
    }
};
