<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEntityAttributesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('entity_attributes', function(Blueprint $table)
		{
			$table->foreign('attribute_group_id', 'entity_attributes_ibfk_1')->references('attribute_group_id')->on('attribute_groups')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('attribute_id', 'entity_attributes_ibfk_2')->references('attribute_id')->on('attributes')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('attribute_set_id', 'entity_attributes_ibfk_3')->references('attribute_set_id')->on('attribute_sets')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('entity_id', 'entity_attributes_ibfk_4')->references('entity_id')->on('entities')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('entity_attributes', function(Blueprint $table)
		{
			$table->dropForeign('entity_attributes_ibfk_1');
			$table->dropForeign('entity_attributes_ibfk_2');
			$table->dropForeign('entity_attributes_ibfk_3');
			$table->dropForeign('entity_attributes_ibfk_4');
		});
	}

}
