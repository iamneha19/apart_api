<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReminderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::dropIfExists('reminder');
        
		Schema::create('reminder', function(Blueprint $table)
		{
			$table->integer('id',true);
            $table->string('alert_unix',50);
            $table->tinyInteger('alert');
            $table->integer('type_id')->unsigned();
            $table->foreign('type_id')->references('id')->on('category');
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
        Schema::dropIfExists('reminder');
	}

}
