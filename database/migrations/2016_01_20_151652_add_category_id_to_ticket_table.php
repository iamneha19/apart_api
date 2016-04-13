<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryIdToTicketTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ticket', function(Blueprint $table)
                {
                    $table->integer('category_id')->unsigned()->nullable()->after('id');

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
            if(Schema::hasColumn('ticket','category_id')) {
                Schema::table('ticket', function(Blueprint $table)
                {
                        $table->dropColumn('category_id');
                });
            }
	}

}
