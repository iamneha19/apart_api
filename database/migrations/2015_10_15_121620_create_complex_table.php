<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComplexTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('complex', function(Blueprint $table) {
			
			$table->integer('id');
			
		  	$table->integer('state_id');
		    $table->integer('city_id');
		    $table->integer('pincode');
		    $table->string('nearest_station');
		    $table->string('landmark');
			
			$table->primary(['id']);
			
			$table
				->foreign('id')
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
		
	}

}
