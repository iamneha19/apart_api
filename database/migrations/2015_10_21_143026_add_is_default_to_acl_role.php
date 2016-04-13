<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDefaultToAclRole extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('acl_role', function(Blueprint $table) {

			$table->tinyInteger('is_default')->after('is_unique')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasColumn('acl_role', 'is_default'))
        {
            Schema::table('acl_role', function(Blueprint $table)
            {
                $table->dropColumn('is_default');
            });
        }
	}

}
