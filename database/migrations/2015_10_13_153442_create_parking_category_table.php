<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParkingCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('parking_category', function(Blueprint $table)
        {
            $table->integer('id', true);
            $table->integer('parent_id')->nullable();
            $table->string('category_name',50);
            
            $table->timestamps();
                    
            $table->foreign('parent_id')
                    ->references('id')
                    ->on('parking_category')
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
		Schema::dropIfExists('parking_category');
	}

}
