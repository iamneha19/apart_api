<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistrictTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('district', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('region_id')
                      ->unsigned();
                $table->string('name');
                $table->timestamps();

                $table->foreign('region_id')
                      ->references('id')
                      ->on('region')
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
		Schema::dropIfExists('district');
	}

}
