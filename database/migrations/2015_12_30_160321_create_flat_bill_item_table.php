<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlatBillItemTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $this->down();

        Schema::create('flat_billing_item', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('society_id');
            $table->integer('flat_id');
            $table->integer('item_id')->unsigned();

            $table->string('month');

            $table->foreign('society_id')
                  ->references('id')
                  ->on('society');

            $table->foreign('flat_id')
                  ->references('id')
                  ->on('flat');

            $table->foreign('item_id')
                  ->references('id')
                  ->on('item')
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
        Schema::dropIfExists('flat_billing_item');
	}

}
