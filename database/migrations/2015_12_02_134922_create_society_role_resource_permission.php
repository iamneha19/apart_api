<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocietyRoleResourcePermission extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('society_role_resource_permission', function(Blueprint $table)
        {
            $table->integer('id', true);
            $table->integer('society_role_id');
            $table->integer('resource_permission_id');
            

            $table->foreign('society_role_id')
                    ->references('id')
                    ->on('society_role')
                    ->onDelete('cascade');
            
            $table->foreign('resource_permission_id')
                    ->references('id')
                    ->on('acl_resource_permission')
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
		Schema::dropIfExists('society_role_resource_permission');
	}

}
