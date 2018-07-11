<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEntityAttributesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('entity_attributes', function(Blueprint $table)
		{
			$table->integer('attribute_id')->unsigned()->index();
			$table->integer('entity_id')->unsigned()->index('entity_attributes_entity_id_foreign');
			$table->integer('attribute_set_id')->unsigned()->index();
			$table->integer('attribute_group_id')->unsigned()->index('entity_attributes_attribute_group_id_foreign');
			$table->unique(['attribute_set_id','attribute_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('entity_attributes');
	}

}
