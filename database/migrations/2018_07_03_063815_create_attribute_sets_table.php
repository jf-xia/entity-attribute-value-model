<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttributeSetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('attribute_sets', function(Blueprint $table)
		{
			$table->increments('attribute_set_id');
			$table->integer('entity_id')->unsigned();
			$table->string('attribute_set_name', 191);
			$table->unique(['entity_id','attribute_set_name']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('attribute_sets');
	}

}
