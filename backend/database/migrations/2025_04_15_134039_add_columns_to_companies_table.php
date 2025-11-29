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
        Schema::table('companies', function (Blueprint $table) {
            $table->string("business_license")->nullable();
            $table->string("business_license_type")->nullable();
            $table->string("business_emirati")->nullable();
            $table->date("business_license_issue_date")->nullable();
            $table->date("business_license_expiry_date")->nullable();
            $table->string("business_makeem_number")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn("business_license");
            $table->dropColumn("business_license_type");
            $table->dropColumn("business_emirati");
            $table->dropColumn("business_license_issue_date");
            $table->dropColumn("business_license_expiry_date");
            $table->dropColumn("business_makeem_number");
        });
    }
};
