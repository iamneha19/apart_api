<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlatTypeToFlat extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('flat', function(Blueprint $table)
            {
              $table->enum('type',array('flat','shop','office'))->nullable()->after('flat_no');

            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            if(Schema::hasColumn('flat','type')) {
                    Schema::table('flat', function(Blueprint $table)
                    {
                            $table->dropColumn('type');
                    });
            }	
	}

}
