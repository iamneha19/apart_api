<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultiselectInFieldManagerBootstrap extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('fm_option', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('tag_id')->unsigned();
            $table->string('value');
        });

		Schema::create('fm_field_type_multiselect', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('value')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('fm_field_type_multiselect');
        Schema::dropIfExists('fm_option');
	}

}
