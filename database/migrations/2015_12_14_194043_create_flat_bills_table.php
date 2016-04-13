<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlatBillsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $this->down();

		Schema::create('flat_bill', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('society_id');
            $table->integer('flat_id');
            $table->integer('bill_id')->index()->nullable();
            $table->integer('charge')->nullable();
            $table->string('month')->index();
            $table->enum('priority', [3, 2, 1]);
            $table->enum('status', ['unpaid', 'paid']);
			$table->timestamps();

            $table->foreign('flat_id')
                  ->references('id')
                  ->on('flat');
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
		Schema::dropIfExists('flat_bill');
	}

}
