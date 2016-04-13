<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBuildingIdToMeeting extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('meeting', function(Blueprint $table)
        {
            $table->integer('building_id')->nullable()->after('society_id');
            $table->foreign('building_id')
                    ->references('id')->on('society')
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
		if (Schema::hasColumn('meeting', 'building_id'))
        {
            Schema::table('meeting', function(Blueprint $table)
            {
                $table->dropForeign('meeting_building_id_foreign');
                $table->dropColumn('building_id');
            });
        }
	}

}
