<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAmentiesMorphTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('amenity_tags', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->integer('society_id');
                    $table->integer('amenity_id')->unsigned();
					
                    $table->morphs('taggable');
                    
                    $table->foreign('amenity_id')
                          ->references('id')
                          ->on('amenity');
					
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
		Schema::dropIfExists('amenity_tags');
	}

}
