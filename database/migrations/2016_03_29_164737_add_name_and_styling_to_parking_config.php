<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameAndStylingToParkingConfig extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('parking_config', function(Blueprint $table) {
                    $table->string('name')
                              ->after('slot_name_prefix')->nullable();
                    $table->string('styling')
                              ->after('name')->nullable();                    
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            if(Schema::hasColumn('parking_config','name','styling')) {
            Schema::table('parking_config', function(Blueprint $table)
                {
                    $table->dropColumn('name','styling');
                });
            }
	}

}
