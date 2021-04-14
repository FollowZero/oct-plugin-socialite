<?php namespace Plus\Socialite\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreatePlusSocialiteFollow extends Migration
{
    public function up()
    {
        Schema::create('plus_socialite_follow', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('uuid')->nullable();
            $table->string('provider');
            $table->string('nickname')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar',500)->nullable();
            $table->text('raw')->nullable();
            $table->text('token_response')->nullable();
            $table->integer('user_id')->unsigned()->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('plus_socialite_follow');
    }
}
