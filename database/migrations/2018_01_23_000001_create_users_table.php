<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email',190)->unique();

            $table->string('address')->default('');
            $table->string('address_addition')->default('');
            $table->string('postcode')->default('');
            $table->string('city')->default('');
            $table->string('country')->default('');
            
            $table->boolean('email_confirmed')->default(0);
            $table->string('email_confirmation_token')->default('');

            $table->integer('role_id')->unsigned()->default(1);
            $table->foreign('role_id')->references('id')->on('roles');

            $table->string('password');

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
