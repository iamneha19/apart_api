<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDivisionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        
            Schema::create('division', function(Blueprint $table)
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
	public function down()
	{
		Schema::dropIfExists('division');
	}

}
