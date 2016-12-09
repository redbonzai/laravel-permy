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

            $table->boolean('is_default')->unsigned()->nullable();

            $table->string('name');
            $table->string('desc');

            // Example (NON-NAMESPACED controller UsersController):
            // syntax: {class_name_wihout_controller}
            // $table->text('users')->nullable();

            // Example (NAMESPACED controller Acme\Admin\UsersController):
            // syntax: {full_class_name_wihout_controller}
            $table->text('acme_admin_users')->nullable();
            $table->text('acme_site_users')->nullable();
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
