<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class UserEmail extends Model
{
    protected $primaryKey = 'character_id';
    protected $table = 'user_emails';
    protected $fillable = ['character_id', 'character_email', 'email_opt_in', 'email_verification_code'];


    public function user()
    {
        return $this->belongsTo('EVEMail\User', 'character_id');
    }
}
