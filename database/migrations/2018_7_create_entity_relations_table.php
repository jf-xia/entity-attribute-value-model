<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEntityRelationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('entity_relations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('entity_id')->unsigned();
			$table->integer('relation_entity_id')->unsigned()->index('fk_relation_entity_id');
			$table->integer('entity_object_id');
			$table->integer('entity_relation_object_id');
			$table->unique(['entity_id','relation_entity_id','entity_object_id','entity_relation_object_id'], 'entity_id_relation_entity_id_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('entity_relations');
	}

}
