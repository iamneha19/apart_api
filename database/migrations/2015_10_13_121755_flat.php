<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Flat extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('flat', function ($table) {
            $table->enum('flat_type',['Super Built Up','Built Up','Carpet Area']);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('flat', function ($table) {
            $table->dropColumn('flat_type');
            });
	}

}
