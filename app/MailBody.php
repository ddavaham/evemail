<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class MailBody extends Model
{
    protected $primaryKey = 'character_id';
    protected $table = 'mail_body';
    protected $fillable = [
        'mail_id',
        'character_id',
        'mail_from',
        'mail_subject',
        'mail_body',
        'mail_labels',
        'is_read',
        'mail_sent',

    ];
}
