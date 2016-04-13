<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSubAmenities extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('sub_amenities', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('society_id');                                                            
                $table->integer('amenity_id')->unsigned();
                $table->string('name');
                $table->foreign('amenity_id')
                      ->references('id')
                      ->on('amenity')
                      ->onDelete('cascade');
                $table->foreign('society_id')
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
		chema::dropIfExists('sub_amenities');
	}

}
