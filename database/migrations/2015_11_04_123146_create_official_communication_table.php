<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateOfficialCommunicationTable extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create ( 'official_communication', function (Blueprint $table) {	
			$table->increments ( 'id' );
			$table->integer ( 'recepient_id' );
			$table->integer ( 'created_by' );
			$table->integer ( 'society_id' );
			$table->string ( 'subject' );
			$table->string ( 'text' );
			$table->string ( 'subject_reference' , 50 );
			$table->string ( 'document_reference' , 50 );
			$table->tinyInteger( 'is_read' );
			$table->enum('status', array('approved', 'unapproved', 'pending'));
			$table->timestamps ();
			
			$table->foreign('society_id')
			->references('id')->on('society')
			->onDelete('cascade');

			$table->foreign('recepient_id')
			->references('id')->on('acl_role')
			->onDelete('cascade');
			
			$table->foreign('created_by')
			->references('id')->on('users')
			->onDelete('cascade');
			
		});
	}	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop ( 'official_communication' );
	}
}
