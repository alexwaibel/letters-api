<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrgUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('org_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('org_id');
            $table->integer('user_id');
            $table->string('role'); // admin, mod, member
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('org_users');
    }
}
