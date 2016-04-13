<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdToSociety extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('society', function(Blueprint $table) {

			$table->integer('parent_id')
				->nullable()
				->after('id');
			
			$table
				->foreign('parent_id')
				->references('id')
				->on('society')
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
		Schema::table('society', function(Blueprint $table) {
			$table->dropForeign('society_parent_id_foreign');
		});
	}

}
