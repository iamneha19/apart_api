<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Amenities extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
			Schema::create ( 'amenities', function (Blueprint $table) {	
			$table->increments ( 'id' );
			$table->integer ( 'user_id' );
			$table->integer ( 'society_id' );
			$table->string ( 'name' );
			$table->string ( 'description' );
			$table->string ( 'image' );
			$table->string ( 'charges' );
			$table->timestamps ();
			
			$table->foreign('society_id')
			->references('id')->on('society')
			->onDelete('cascade');
			
			$table->foreign('user_id')
			->references('id')->on('users')
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
		Schema::drop ( 'amenities' );
	}

}
