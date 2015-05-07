<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('char_name');
			$table->string('email')->unique()->nullable();
			$table->string('password', 60);
            $table->unsignedInteger('char_id');
            $table->string('slack_id')->nullable();
            $table->string('slack_name')->nullable();
            $table->string('status')->nullable();
            $table->integer('error')->nullable();
            $table->dateTime('next_check')->nullable();
            $table->string('corp_id')->nullable();
            $table->string('corp_name')->nullable();
            $table->string('alliance_id')->nullable();
            $table->string('alliance_name')->nullable();
            $table->boolean('admin')->default(false);
            $table->boolean('super_admin')->default(false);
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
		Schema::drop('users');
	}

}
