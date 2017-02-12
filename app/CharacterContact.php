<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class CharacterContact extends Model
{
    protected $primaryKey = 'contact_id';
    protected $table = 'character_contact';
    protected $fillable = [
        'character_id',
        'contact_id',
        'contact_name',
        'contact_type',
        'is_blocked',
        'is_watched',
        'standing',
    ];
}
