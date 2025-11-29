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
        Schema::table('customer_alarm_notes', function (Blueprint $table) {
            $table->integer("security_id")->nullable();
            $table->integer("contact_id")->nullable();
            $table->string("call_status")->nullable();
            $table->string("response")->nullable();
            $table->string("event_status")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_alarm_notes', function (Blueprint $table) {
            $table->dropColumn("security_id");
            $table->dropColumn("contact_id");
            $table->dropColumn("call_status");
            $table->dropColumn("response");
            $table->dropColumn("event_status");
        });
    }
};
