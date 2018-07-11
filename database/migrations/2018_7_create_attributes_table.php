<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAttributesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('attributes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('entity_id')->unsigned();
			$table->string('attribute_code', 50);
			$table->string('backend_class', 191)->nullable();
			$table->string('backend_type', 191);
			$table->string('backend_table', 191)->nullable();
			$table->string('frontend_class', 191)->nullable();
			$table->string('frontend_type', 191);
			$table->string('frontend_label', 191)->nullable();
			$table->string('source_class', 191)->nullable();
			$table->string('default_value', 191)->nullable();
			$table->boolean('not_list')->nullable()->default(0);
			$table->boolean('not_report')->nullable()->default(0);
			$table->boolean('is_unique')->nullable()->default(0);
			$table->boolean('is_filterable')->default(0);
			$table->boolean('is_searchable')->default(0);
			$table->boolean('is_required')->default(0);
			$table->smallInteger('order')->nullable();
			$table->string('form_field_html', 600)->nullable();
			$table->string('list_field_html', 600)->nullable();
			$table->string('required_validate_class', 191)->nullable();
			$table->string('help')->nullable();
			$table->string('placeholder')->nullable();
			$table->unique(['entity_id','attribute_code']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('attributes');
	}

}
