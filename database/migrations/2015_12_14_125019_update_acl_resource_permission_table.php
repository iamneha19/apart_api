<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAclResourcePermissionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('acl_resource_permission', function(Blueprint $table)
        {
            $table->tinyInteger('type')->default(0);
            
           
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		
        Schema::table('acl_resource_permission', function(Blueprint $table)
        {
            $table->dropColumn('type');
        });
        
	}

}
