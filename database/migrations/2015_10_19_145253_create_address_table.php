<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('address_detail', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('city_id');
            $table->text('street');
            $table->string('landmark');
            $table->string('nearest_station');
            $table->integer('pincode')->unsigned();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('address_detail');
	}

}
