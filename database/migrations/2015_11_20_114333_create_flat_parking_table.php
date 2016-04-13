<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlatParkingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('flat_parking', function(Blueprint $table)
		{
			$table->integer('id',true);
            $table->integer('parking_slot_id');
            $table->foreign('parking_slot_id')->references('id')->on('parking_slot');
            $table->integer('flat_id');
            $table->foreign('flat_id')->references('id')->on('flat');
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
