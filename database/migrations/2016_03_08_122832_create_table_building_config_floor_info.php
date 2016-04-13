<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBuildingConfigFloorInfo extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('building_config_floor_info', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('building_configuration_id')->unsigned();                                                            
                $table->integer('floor_no');
                $table->integer('no_of_flat');
                $table->foreign('building_configuration_id')
                      ->references('id')
                      ->on('building_configuration')
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
		Schema::dropIfExists('building_config_floor_info');
	}

}
