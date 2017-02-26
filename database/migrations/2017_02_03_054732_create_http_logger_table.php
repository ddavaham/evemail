<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHttpLoggerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('http_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('request_id');
            $table->boolean('error');
            $table->integer('errorCode');
            $table->string('errorMessage', 512)->nullable();
            $table->boolean('curlError');
            $table->integer('curlErrorCode');
            $table->string('curlErrorMessage', 512)->nullable();
            $table->boolean('httpError');
            $table->integer('httpStatusCode');
            $table->string('httpErrorMessage', 512)->nullable();
            $table->string('baseUrl', 512);
            $table->string('url', 512);
            $table->text('options');
            $table->text('response');
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
        Schema::dropIfExists('http_loggers');
    }
}
