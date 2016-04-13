<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStateIdDivisionIdAndRegionIdToDistrictTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('district', function(Blueprint $table)
            {
                    $table->integer('state_id')->unsigned()->nullable()->after('name');

                    $table->foreign('state_id')
                              ->references('id')
                              ->on('state')
                              ->onDelete('cascade');
                    $table->integer('division_id')->unsigned()->nullable();

                    $table->foreign('division_id')
                              ->references('id')
                              ->on('division')
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
            if(Schema::hasColumn('district','state_id')) {
                    Schema::table('district', function(Blueprint $table)
                    {
                            $table->dropColumn('state_id');
                    });
            }
            if(Schema::hasColumn('district','division_id')) {
                    Schema::table('district', function(Blueprint $table)
                    {
                            $table->dropColumn('division_id');
                    });
            }
	}

}
