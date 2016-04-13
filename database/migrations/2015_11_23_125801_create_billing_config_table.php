<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingConfigTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('billing_config', function(Blueprint $table)
        {
            $table->increments('id');

            $table->integer('society_id');

            $table->string('key');

            $table->string('value');

            $table->foreign('society_id')
                  ->references('id')
                  ->on('society');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('billing_config');
	}

}
