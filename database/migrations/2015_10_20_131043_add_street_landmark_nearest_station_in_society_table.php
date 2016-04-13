<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStreetLandmarkNearestStationInSocietyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('society', function(Blueprint $table)
        {
            $table->string('street')->after('address')->nullable();
            $table->string('landmark')->after('street')->nullable();
            $table->string('nearest_station')->after('landmark')->nullable();
    });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('society', function(Blueprint $table)
        {
            $table->dropColumn(['street', 'landmark', 'nearest_station']);
        });
	}

}
