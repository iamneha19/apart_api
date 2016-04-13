<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Category extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('category', function(Blueprint $table)
            {
                $table->increments('id')->unsigned();
                $table->string('name');
                $table->string('description');
                $table->enum('type', ['society', 'society_document','meeting','official_communication','flat_document','task_category','helpdesk_category']);
                $table->integer('society_id')->nullable();
                $table->tinyInteger('is_mandatory');
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
		Schema::drop('category');
	}

}
