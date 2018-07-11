<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAttributeOptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('attribute_options', function(Blueprint $table)
		{
			$table->foreign('attribute_id', 'attribute_options_ibfk_1')->references('id')->on('attributes')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('attribute_options', function(Blueprint $table)
		{
			$table->dropForeign('attribute_options_ibfk_1');
		});
	}

}
