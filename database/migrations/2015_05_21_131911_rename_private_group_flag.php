<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePrivateGroupFlag extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('channels', function(Blueprint $table)
        {
            $table->renameColumn('private', 'is_group');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('channels', function(Blueprint $table)
        {
            $table->renameColumn('is_group', 'private');
        });
	}

}
