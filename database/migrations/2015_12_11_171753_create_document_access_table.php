<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentAccessTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('file_access', function(Blueprint $table)
		{
			$table->integer('id',true);
            $table->integer('flat_id')->nullable();
            $table->foreign('flat_id')->references('id')->on('flat');
            $table->integer('file_id');
            $table->foreign('file_id')->references('id')->on('file');
			$table->integer('role_id');
            $table->foreign('role_id')->references('id')->on('acl_role');
            $table->enum('status', array('1', '0'));
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
		//
	}

}
