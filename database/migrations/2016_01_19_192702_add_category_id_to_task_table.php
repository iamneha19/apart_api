<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryIdToTaskTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('task', function(Blueprint $table)
                {
                    $table->integer('task_category_id')->unsigned()->nullable()->after('society_id');

                    $table->foreign('task_category_id')
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
            if(Schema::hasColumn('task','task_category_id')) {
                Schema::table('task', function(Blueprint $table)
                {
                        $table->dropColumn('task_category_id');
                });
            }
	}

}
