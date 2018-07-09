<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEntityRelationIdsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('entity_relation_ids', function(Blueprint $table)
		{
			$table->foreign('entity_relation_id', 'fk_entity_relation_id')->references('id')->on('entity_relations')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('entity_relation_ids', function(Blueprint $table)
		{
			$table->dropForeign('fk_entity_relation_id');
		});
	}

}
