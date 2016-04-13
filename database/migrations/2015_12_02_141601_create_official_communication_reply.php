<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficialCommunicationReply extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create ( 'official_communication_reply', function (Blueprint $table) {                                         
			$table->integer ( 'id', true );
			$table->integer ( 'user_id' );
			$table->integer ( 'society_id' );
			$table->integer ( 'letter_id' )->unsigned();
			$table->string ( 'comment' );
			$table->timestamps ();
				
			$table->foreign('society_id')
			->references('id')->on('society')
			->onDelete('cascade');
				
			$table->foreign('user_id')
			->references('id')->on('users')
			->onDelete('cascade');
				
			$table->foreign('letter_id')
			->references('id')->on('official_communication')
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
		Schema::drop ( 'official_communication_reply' );
	}

}
