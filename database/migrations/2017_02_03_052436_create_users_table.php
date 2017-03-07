<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('character_id');
            $table->string('character_name');
            $table->integer('corporation_id');
            $table->integer('alliance_id')->nullable();
            $table->text('preferences')->nullable();
            $table->string('time_zone')->default('UTC');
            $table->string('time_notation')->nullable();
            $table->boolean('is_new')->default(1);
            $table->timestamps();


            $table->primary(['character_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
