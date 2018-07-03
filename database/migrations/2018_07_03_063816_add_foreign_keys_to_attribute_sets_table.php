<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAttributeSetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('attribute_sets', function(Blueprint $table)
		{
			$table->foreign('entity_id', 'attribute_sets_ibfk_1')->references('entity_id')->on('entities')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('attribute_sets', function(Blueprint $table)
		{
			$table->dropForeign('attribute_sets_ibfk_1');
		});
	}

}
