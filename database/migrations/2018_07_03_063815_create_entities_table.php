<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEntitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('entities', function(Blueprint $table)
		{
			$table->increments('entity_id');
			$table->string('entity_code', 50)->unique();
			$table->string('entity_name');
			$table->string('entity_class', 191);
			$table->string('entity_table', 191);
			$table->integer('default_attribute_set_id')->unsigned()->nullable();
			$table->string('additional_attribute_table', 191)->nullable();
			$table->boolean('is_flat_enabled')->nullable()->default(0);
			$table->string('entity_desc', 2550)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('entities');
	}

}
