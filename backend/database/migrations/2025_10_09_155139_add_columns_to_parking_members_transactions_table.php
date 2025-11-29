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
        Schema::table('parking_members_transactions', function (Blueprint $table) {
            $table->timestamp("created_datetime")->nullable();
            $table->decimal("credit", 8, 2)->default(0)->change();
            $table->decimal("debit", 8, 2)->default(0)->change();
            $table->string("notes")->nullable()->change();
            $table->integer("parking_log_id")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parking_members_transactions', function (Blueprint $table) {
            //
        });
    }
};
