<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStateIdToRegionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('region', function(Blueprint $table)
            {
                    $table->integer('state_id')->unsigned()->nullable()->after('name');

                    $table->foreign('state_id')
                              ->references('id')
                              ->on('state')
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
            if(Schema::hasColumn('region','state_id')) {
                    Schema::table('region', function(Blueprint $table)
                    {
                            $table->dropColumn('state_id');
                    });
            }
	}

}
