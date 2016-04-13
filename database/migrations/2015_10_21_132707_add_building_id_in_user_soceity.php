<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddBuildingIdInUserSoceity extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_society', function(Blueprint $table)
		{
			$table->integer('building_id')->nullable();
			
			$table->foreign('building_id')
				  ->references('id')
				  ->on('building')
				  ->onDelete('cascade');
			
		});
		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if(Schema::hasColumn('user_society','building_id')) {
			Schema::table('user_society', function(Blueprint $table)
			{
				$table->dropColumn('building_id');
			});
		}
	}

}
