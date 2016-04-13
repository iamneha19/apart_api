<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResourceFieldToSocietyRoleResource extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('society_role_resource', function(Blueprint $table)
        {

            $table->integer('id', true);
            $table->integer('society_role_id');
            $table->string('resource',45);

            $table->foreign('society_role_id')
                  ->references('id')
                  ->on('society_role');

            $table->foreign('resource')
                  ->references('acl_name')
                  ->on('acl_resource');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('society_role_resource');
	}

}
