<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoOfBuildingsToSociety extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('society', function(Blueprint $table) {
			$table->integer('no_of_buildings')
				  ->after('google_map_src');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            if(Schema::hasColumn('society','no_of_buildings')) {
            Schema::table('society', function(Blueprint $table)
                {
                    $table->dropColumn('no_of_buildings');
                });
            }
	}

}
