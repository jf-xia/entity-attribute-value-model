<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEntityRelationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('entity_relations', function(Blueprint $table)
		{
			$table->foreign('entity_id', 'fk_entity_id')->references('id')->on('entities')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('relation_entity_id', 'fk_relation_entity_id')->references('id')->on('entities')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('entity_relations', function(Blueprint $table)
		{
			$table->dropForeign('fk_entity_id');
			$table->dropForeign('fk_relation_entity_id');
		});
	}

}
