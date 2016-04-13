<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillPaymentTable extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('flat_bill_id')->unsigned();
            $table->enum('payment_type', ['cash', 'cheque']);
            $table->string('cheque_number')->nullable();
            $table->timestamps();

            $table->foreign('flat_bill_id')
                  ->references('id')
                  ->on('flat_bill');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('payment');
	}

}
