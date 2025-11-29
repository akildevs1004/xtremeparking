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
        Schema::table('tickets', function (Blueprint $table) {
            $table->integer("category_id")->nullable();
            $table->integer("job_start_verified_contact_id")->nullable();
            $table->dateTime("job_start_datetime")->nullable();
            $table->dateTime("job_end_datetime")->nullable();
            $table->integer("job_end_verified_contact_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn("category_id");
            $table->dropColumn("job_start_verified_contact_id");
            $table->dropColumn("job_start_datetime");
            $table->dropColumn("job_end_datetime");
            $table->dropColumn("job_end_verified_contact_id");
        });
    }
};
