<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsUniqueToSocietyRole extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('society_role', function(Blueprint $table) {

			$table->tinyInteger('is_unique')->after('role_title')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasColumn('society_role', 'is_unique'))
        {
            Schema::table('society_role', function(Blueprint $table)
            {
                $table->dropColumn('is_unique');
            });
        }
	}

}
