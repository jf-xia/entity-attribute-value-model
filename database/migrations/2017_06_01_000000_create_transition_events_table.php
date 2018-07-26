<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransitionEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transition_events', function(Blueprint $table) {
            $table->increments('id');
            $table->string('event')->nullable();
            $table->string('from')->nullable();
            $table->string('to');
            // $table->string('custom_attribute_field')->nullable();
            $table->integer('stateful_id');
            $table->string('stateful_type')->nullable();
            // $table->foreign('stateful_id')->references('id')->on('stateful_model');//Optional foreign key
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transition_events');
    }
}