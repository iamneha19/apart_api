<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParkingConfigTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('parking_config', function(Blueprint $table)
		{
			$table->integer('id',true);
            $table->tinyInteger('stack_row');
            $table->tinyInteger('stack_column');
            $table->tinyInteger('total_slot');
            $table->integer('slot_charges');
            $table->string('slot_name_prefix', 10);
            $table->integer('category_id');
            $table->foreign('category_id')->references('id')->on('parking_category');
            $table->integer('society_id');
            $table->foreign('society_id')->references('id')->on('society');
			$table->timestamps();
		});

        Schema::table('parking_slot', function(Blueprint $table)
        {
            $table->foreign('parking_config_id')->references('id')->on('parking_config');
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
