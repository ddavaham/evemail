<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class UserPreferences extends Model
{
    protected $primaryKey = 'character_id';
    protected $table = 'user_preferences';
    protected $fillable = ['character_id', 'new_evemail'];

    public function user()
    {
        return $this->belongsTo('EVEMail\User', 'character_id');
    }
}
