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
		Schema::table('acl_resource_permission', function($table)
        {
            $table->string('title',45)->after('resource_acl_name');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('acl_resource_permission', 'title'))
        {
            Schema::table('acl_resource_permission', function(Blueprint $table)
            {

                $table->dropColumn('title');
            });
        }
	}

}
