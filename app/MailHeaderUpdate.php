<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class MailHeaderUpdate extends Model
{
    protected $table = 'mail_header_update';
    protected $primaryKey = 'character_id';
    protected $fillable = [
        'character_id', 'last_header_update'
    ];
}
