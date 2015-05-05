<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('channel', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('slack_id');
            $table->string('name');
            $table->boolean('private');
			$table->timestamps();
		});
        Schema::create('channel_group', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('channel_id');
            $table->unsignedInteger('group_id');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('channel');
        Schema::drop('channel_group');
	}

}
