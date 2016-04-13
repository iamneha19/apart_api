<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAclRoleResourcePermissionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('acl_role_resource_permission', function(Blueprint $table)
        {
            $table->integer('acl_role_id');
            $table->dropForeign('FK_society_id');
            $table->dropColumn(['society_id','role_acl_name']);
            
          
            $table->foreign('acl_role_id')
                    ->references('id')->on('acl_role')
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
		//
	}

}
