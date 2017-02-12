<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailLabelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_label', function (Blueprint $table) {
            $table->bigInteger('character_id');
            $table->integer('label_id');
            $table->string('label_name');
            $table->int('label_unread_count');
            $table->timestamps();

            $table->primary(['character_id', 'label_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_label');
    }
}
