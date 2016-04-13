<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCityTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('city', function(Blueprint $table)
		{
            $table->increments('id');
            $table->integer('state_id')
                  ->unsigned();
            $table->string('name');
            $table->timestamps();

            $table->foreign('state_id')
                  ->references('id')
                  ->on('state')
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
		Schema::dropIfExists('city');
	}

}
