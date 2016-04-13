<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMeetingAttendeeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('meeting_attendee', function($table)
        {
            $table->dropForeign('FK_meeting_attendee_user');
            $table->dropColumn('user_id');
            $table->integer('role_id');
            $table->foreign('role_id')->references('id')->on('acl_role');
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
