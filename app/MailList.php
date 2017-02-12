<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class MailList extends Model
{
    protected $primaryKey = 'mailing_list_id';
    protected $table = 'mailing_list';
    protected $fillable = [
        'character_id',
        'mailing_list_id',
        'mailing_list_name'
    ];
}
