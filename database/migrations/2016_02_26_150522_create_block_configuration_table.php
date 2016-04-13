<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlockConfigurationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('block_configuration', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->integer('block_id');                         
                    $table->enum('is_flat_same_on_each_floor',[YES,NO]);                   
                    $table->integer('flat_on_each_floor'); 
                    $table->foreign('block_id')
                          ->references('id')
                          ->on('block')
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
		Schema::dropIfExists('block_configuration');
	}

}
