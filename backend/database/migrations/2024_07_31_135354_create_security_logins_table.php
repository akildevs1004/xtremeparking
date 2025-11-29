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
        Schema::create('security_logins', function (Blueprint $table) {
            $table->id();
            $table->integer("company_id");
            $table->integer("branch_id");
            $table->string("first_name");
            $table->string("last_name");
            $table->string("contact_number")->nullable();
            $table->integer("user_id")->nullable();
            $table->json("customers_list")->nullable();




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
        Schema::dropIfExists('security_logins');
    }
};
