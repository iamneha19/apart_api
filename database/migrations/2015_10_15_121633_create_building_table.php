<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuildingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('building', function(Blueprint $table) {
			
			$table->integer('id');
			$table->tinyinteger('flats');
			$table->tinyinteger('floors');
			$table->tinyinteger('blocks');
			$table->string('name');
			
			$table->primary(['id']);
			
			$table->foreign('id')
				  ->references('id')
				  ->on('society')
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
		//
	}

}
