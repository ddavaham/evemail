<?php

namespace EVEMail;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'character_id';
    protected $fillable = [
        "character_id","character_name","corporation_id","alliance_id","time_zone","time_notation", 'is_new', 'preferences'
    ];

    public function getRememberToken()
    {
        return null; // not supported
    }

    public function setRememberToken($value)
    {
        // not supported
    }

    public function getRememberTokenName()
    {
        return null; // not supported
    }

    /**
    * Overrides the method to ignore the remember token.
    */
    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();
        if (!$isRememberTokenAttribute)
        {
          parent::setAttribute($key, $value);
        }
    }

    public function email ()
    {
        return $this->hasOne('EVEMail\UserEmail', 'character_id');
    }
    public function preferences()
    {
        return json_decode($this->preferences, true);
    }
    public function token ()
    {
        return $this->hasOne('EVEMail\Token', 'character_id');
    }

}
