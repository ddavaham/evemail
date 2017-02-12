<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class MailHeader extends Model
{
    protected $primaryKey = 'character_id';
    protected $table = 'mail_header';
    protected $fillable = [
        'character_id','mail_id','mail_subject','mail_sender','mail_sent_date','mail_labels','mail_recipient','is_read'
    ];

}
