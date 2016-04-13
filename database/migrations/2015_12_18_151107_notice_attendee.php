<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NoticeAttendee extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create ( 'notice_attendee', function (Blueprint $table) {
			$table->integer ( 'id', true );
			$table->integer ( 'notice_id' );
			$table->integer ( 'role_id' );
			$table->tinyInteger ( 'active_status' );
			$table->tinyInteger ( 'status' );
			$table->timestamps ();

			$table->foreign('notice_id')
			->references('id')->on('notice')
			->onDelete('cascade');

			$table->foreign('role_id')
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
		Schema::drop ( 'notice_attendee' );
	}

}
