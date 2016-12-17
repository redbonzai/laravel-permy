<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permy', function(Blueprint $table)
        {
            $table->increments('id');

            $table->string('name');
            $table->string('desc');

            // Example (NON-NAMESPACED controller UsersController):
            // syntax: {controller}
            // $table->text('users')->nullable();

            // Example (NAMESPACED controller Acme\Admin\UsersController):
            // syntax: {acme::namespace::controller}
            $table->text('acme::admin::users')->nullable();
            $table->text('acme::site::users')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('permy');
    }
}
