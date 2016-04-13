<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBlockConfigFloorInfo extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('block_config_floor_info', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->integer('block_configuration_id')->unsigned();                                  
                    $table->integer('floor_no');
                    $table->integer('no_of_flat');
                    $table->foreign('block_configuration_id')
                          ->references('id')
                          ->on('block_configuration')
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
		Schema::dropIfExists('block_configration_floor_info');
	}

}
