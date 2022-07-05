<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttributeGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('attribute_groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('attribute_set_id')->unsigned();
			$table->string('attribute_group_name', 191);
			$table->integer('order')->default(0);
			$table->unique(['attribute_set_id','attribute_group_name']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('attribute_groups');
	}

}
