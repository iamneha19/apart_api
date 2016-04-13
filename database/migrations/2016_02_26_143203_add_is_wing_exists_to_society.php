<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsWingExistsToSociety extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('society', function(Blueprint $table) {
			$table->enum('is_wing_exists',[0,1])
				  ->after('no_of_buildings');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            if(Schema::hasColumn('society','is_wing_exists')) {
                Schema::table('society', function(Blueprint $table)
                    {
                        $table->dropColumn('is_wing_exists');
                    });
                }
	}

}
