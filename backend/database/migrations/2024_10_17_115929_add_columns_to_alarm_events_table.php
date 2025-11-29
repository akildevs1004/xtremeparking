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
        Schema::table('alarm_events', function (Blueprint $table) {
            $table->string("security_name")->nullable();
            $table->string("security_email")->nullable();
            $table->string("security_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alarm_events', function (Blueprint $table) {
            $table->dropColumn("security_name");
            $table->dropColumn("security_email");
            $table->dropColumn("security_id");
        });
    }
};
