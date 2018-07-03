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
			$table->integer('relation_id', true);
			$table->integer('entity_id');
			$table->string('relation_type', 55);
			$table->integer('relation_entity_id');
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
