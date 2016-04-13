<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNosFloorsToBlockConfiguration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('block_configuration', function(Blueprint $table) {
			$table->integer('nos_of_floors')
				  ->after('block_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if(Schema::hasColumn('block_configuration','nos_of_floors')) {
                Schema::table('block_configuration', function(Blueprint $table)
                    {
                        $table->dropColumn('nos_of_floors');
                    });
                }
	}

}
