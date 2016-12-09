<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermyUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('permy_user', function(Blueprint $table)
        {
            $table->increments('id');

            $table->integer('permy_id')->unsigned()->index();
            $table->foreign('permy_id')->references('id')->on('permy')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('permy_user');
	}

}
