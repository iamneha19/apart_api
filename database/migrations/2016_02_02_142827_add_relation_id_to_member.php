<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelationIdToMember extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('member', function(Blueprint $table)
            {
                $table->integer('relation_id')->unsigned()->nullable()->after('id');

                $table->foreign('relation_id')
                          ->references('id')
                          ->on('category')
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
            if(Schema::hasColumn('member','relation_id')) {
                Schema::table('ticket', function(Blueprint $table)
                {
                        $table->dropColumn('relation_id');
                });
                }
        }

}
