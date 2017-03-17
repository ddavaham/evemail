<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInactiveColumnToMailRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mail_recipient', function (Blueprint $table) {
            $table->boolean('inactive')->nullable()->after('recipient_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mail_recipient', function (Blueprint $table) {
            //
        });
    }
}
