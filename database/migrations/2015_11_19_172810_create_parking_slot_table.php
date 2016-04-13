<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParkingSlotTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('parking_slot', function(Blueprint $table)
		{
			$table->integer('id',true);
			$table->string('slot_name');
			$table->integer('vehicle_type');
            $table->foreign('vehicle_type')->references('id')->on('vehicle_type');
            $table->integer('category_id');
            $table->foreign('category_id')->references('id')->on('parking_category');
            $table->integer('society_id');
            $table->foreign('society_id')->references('id')->on('society');
            $table->integer('parking_config_id');
            $table->enum('status', array('1', '0'));
			$table->timestamps();
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
