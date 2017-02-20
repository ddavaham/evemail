<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailingListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailing_list', function (Blueprint $table) {
            $table->integer('character_id');
            $table->integer('mailing_list_id');
            $table->string('mailing_list_name');
            $table->timestamps();

            
            $table->primary(['mailing_list_id', 'character_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailing_list');
    }
}
