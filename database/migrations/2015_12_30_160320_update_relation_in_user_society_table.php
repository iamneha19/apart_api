<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRelationInUserSocietyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_society', function($table)
        {
            DB::statement("ALTER TABLE user_society MODIFY COLUMN relation ENUM('owner','tenant','associate')");
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_society', function($table)
        {
            DB::statement("ALTER TABLE user_society MODIFY COLUMN relation ENUM('owner','tenant','vacant','builder')");
        });
	}

}
