<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class MailRecipient extends Model
{
    protected $primaryKey = "recipient_id";
    protected $table = 'mail_recipient';
    protected $fillable = [
        'character_id', 'recipient_id', 'recipient_name', 'recipient_type'
    ];
}
