<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubAmenityIdToAmenityTags extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('amenity_tags', function(Blueprint $table) {
			$table->integer('sub_amenity_id')
				  ->after('amenity_id')->nullable()->unsigned();
                        $table->foreign('sub_amenity_id')
                                ->references('id')
                                ->on('sub_amenities')
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
		if(Schema::hasColumn('amenity_tags','sub_amenity_id')) {
                Schema::table('amenity_tags', function(Blueprint $table)
                    {
                        $table->dropColumn('sub_amenity_id');
                    });
                }
	}

}
