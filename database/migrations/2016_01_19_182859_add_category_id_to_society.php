<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryIdToSociety extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('society', function(Blueprint $table)
            {
                    $table->integer('society_category_id')->unsigned()->nullable()->after('city_id');

                    $table->foreign('society_category_id')
                              ->references('id')
                              ->on('category')
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
            if(Schema::hasColumn('society','society_category_id')) {
                    Schema::table('society', function(Blueprint $table)
                    {
                            $table->dropColumn('society_category_id');
                    });
            }
	}

}
