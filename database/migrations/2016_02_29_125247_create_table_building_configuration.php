<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBuildingConfiguration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('building_configuration', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->integer('building_id');
                    $table->integer('no_of_floor');
                    $table->enum('is_flat_same_on_each_floor',['YES','NO']);                     
                    $table->integer('flat_on_each_floor');
                    $table->foreign('building_id')
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
		Schema::dropIfExists('building_configuration');
	}

}
