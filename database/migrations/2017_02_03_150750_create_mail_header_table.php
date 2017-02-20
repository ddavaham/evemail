<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_header', function (Blueprint $table) {
            $table->bigInteger('character_id');
            $table->bigInteger('mail_id');
            $table->string('mail_subject');
            $table->bigInteger('mail_sender');
            $table->timestamp('mail_sent_date')->nullable();
            $table->text('mail_labels')->nullable();
            $table->integer('mailing_list')->nullable();
            $table->text('mail_recipient');
            $table->boolean('is_read');
            $table->boolean('is_known')->default(0);
            $table->timestamps();

            $table->primary(['mail_id', 'character_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_header');
    }
}
