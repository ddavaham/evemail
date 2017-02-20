<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_recipient', function (Blueprint $table) {
            $table->bigInteger('character_id');
            $table->bigInteger('recipient_id');
            $table->string('recipient_name');
            $table->string('recipient_type');
            $table->timestamps();

            $table->primary(['recipient_id', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_recipients');
    }
}
