<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('sales_inquiries', function (Blueprint $table) {
            DB::statement("ALTER TABLE sales_inquiries ALTER COLUMN sensor_count SET DEFAULT 0");
            DB::statement("ALTER TABLE sales_inquiries ALTER COLUMN sensor_count TYPE INTEGER USING sensor_count::integer");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_inquiries', function (Blueprint $table) {
            //
        });
    }
};
