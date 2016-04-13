<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocietyConfigTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('society_config', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('society_id');
            $table->integer('building_count', false, true);
            $table->enum('is_approved', ['NO', 'YES']);
            $table->integer('approved_by')->nullable();
            $table->text('notes');

            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('society_config');
	}

}
