<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacterContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('character_contact', function (Blueprint $table) {
            $table->integer('character_id');
            $table->integer('contact_id');
            $table->string('contact_type');
            $table->boolean('is_blocked');
            $table->boolean('is_watched');
            $table->float('standing', 3,1);
            $table->timestamps();

            $table->primary(['contact_id', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('character_contact');
    }
}
