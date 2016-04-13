<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdToRole extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('acl_role', function(Blueprint $table)
        {
            $table->integer('parent_id')->nullable();
            $table->foreign('parent_id')
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
        if (Schema::hasColumn('acl_role', 'parent_id'))
        {
            Schema::table('acl_role', function(Blueprint $table)
            {
                $table->dropForeign('acl_role_parent_id_foreign');
                $table->dropColumn('parent_id');
            });
        }
        
             
        
		
	}

}
