<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocietyRoleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('society_role', function(Blueprint $table)
        {
            $table->integer('id', true);
            $table->string('role_title',50);
            
            $table->timestamps();
            $table->softDeletes();
           
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('society_role');
	}

}
