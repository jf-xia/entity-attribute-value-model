<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttributeOptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('attribute_options', function(Blueprint $table)
		{
			$table->increments('option_id');
			$table->integer('attribute_id')->unsigned()->index('attribute_options_attribute_id_foreign');
			$table->string('label', 191);
			$table->string('value', 191);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('attribute_options');
	}

}
