<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleMapSrcToSocietyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('society', function(Blueprint $table)
               {
                   $table->string('google_map_src')->nullable()->after('name');
                   
               });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		 if(Schema::hasColumn('society','google_map_src')) {
                Schema::table('society', function(Blueprint $table)
                {
                        $table->dropColumn('google_map_src');
                });
            }
	}

}
