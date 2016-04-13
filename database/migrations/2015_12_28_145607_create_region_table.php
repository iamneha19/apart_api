<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('region', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('division_id')
                      ->unsigned();
                $table->string('name');
                $table->timestamps();

                $table->foreign('division_id')
                      ->references('id')
                      ->on('division')
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
		Schema::dropIfExists('region');
	}

}
