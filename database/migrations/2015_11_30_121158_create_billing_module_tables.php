<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingModuleTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('billing', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('society_id');
            $table->integer('office_charge');
            $table->integer('residential_charge');
            $table->integer('shop_charge');
            $table->date('month');

            $table->timestamps();

            $table->foreign('society_id')
                  ->references('id')
                  ->on('society');
        });

		Schema::create('item', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('society_id');
            $table->string('name');
            $table->string('flat_category');
            $table->string('charge');
            $table->enum('fixed_billing_item', ['YES', 'NO']);
            $table->date('month')->nullable();

            $table->timestamps();

            $table->foreign('society_id')
                  ->references('id')
                  ->on('society');
        });

		Schema::create('billable_flat', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('flat_id');

            $table->morphs('bill_category');

            $table->foreign('flat_id')
                  ->references('id')
                  ->on('flat');
        });

		Schema::create('billable_building', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('building_id');

            $table->morphs('bill_category');

            $table->foreign('building_id')
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
		Schema::dropIfExists('billing');
		Schema::dropIfExists('item');
		Schema::dropIfExists('billable_flat');
		Schema::dropIfExists('billable_building');
	}

}
