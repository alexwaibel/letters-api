<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('addr_line_1');
            $table->string('addr_line_2');
            $table->string('city');
            $table->string('state');
            $table->string('postal');

            $table->float('credit')->default(4);

            $table->string('type')->default('user'); // user, admin

            $table->string('profile_img_path')->default('avatar.svg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
