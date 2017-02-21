<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class MailLabel extends Model
{
    //protected $primaryKey = ['character_id', 'label_id'];
    protected $table = 'mail_label';
    protected $fillable = [
        'character_id', 'label_id', 'label_name', 'label_unread_count'
    ];
}
