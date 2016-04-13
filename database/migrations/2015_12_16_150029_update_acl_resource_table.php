<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAclResourceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('acl_resource', function(Blueprint $table)
        {
            $table->tinyInteger('access_level')->default(0);
            
           
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('acl_resource', function(Blueprint $table)
        {
            $table->dropColumn('access_level');
        });
	}

}
