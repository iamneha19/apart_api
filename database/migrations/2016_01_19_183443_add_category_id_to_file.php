<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AddCategoryIdToFile extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('file', function(Blueprint $table)
            {
                $table->integer('category_id')->unsigned()->nullable()->after('folder_type');
                $table->foreign('category_id')
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
            if(Schema::hasColumn('file','category_id')) {
                Schema::table('file', function(Blueprint $table)
                {
                        $table->dropColumn('category_id');
                });
            }
	}

}
