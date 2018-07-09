<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEntityRelationIdsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('entity_relation_ids', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('entity_relation_id')->unsigned();
			$table->integer('entity_object_id')->unsigned();
			$table->integer('entity_relation_object_id')->unsigned();
			$table->unique(['entity_relation_id','entity_object_id','entity_relation_object_id'], 'entity_relation_id
entity_object_id
unique_relation_object_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('entity_relation_ids');
	}

}
