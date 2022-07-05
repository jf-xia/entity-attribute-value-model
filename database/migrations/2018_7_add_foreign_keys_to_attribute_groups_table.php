<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAttributeGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('attribute_groups', function(Blueprint $table)
		{
			$table->foreign('attribute_set_id', 'attribute_groups_ibfk_1')->references('id')->on('attribute_sets')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('attribute_groups', function(Blueprint $table)
		{
			$table->dropForeign('attribute_groups_ibfk_1');
		});
	}

}
