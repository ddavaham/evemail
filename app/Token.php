<?php

namespace EVEMail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = "token";

    protected $primaryKey = 'character_id';

    protected $fillable = [
        'character_id', 'access_token', 'refresh_token', 'token_expiry', 'scopes', 'token_type', 'disabled'
    ];

    public static function update_token($character_data,$token_data)
    {
        $update_or_create_token = self::updateOrCreate([
            'character_id'=> $character_data->CharacterID
        ], [
            'character_id' => $character_data->CharacterID,
            'access_token' => $token_data->access_token,
            'refresh_token' => $token_data->refresh_token,
            'token_expiry' => Carbon::now()->addMinutes(20)->toDateTimeString(),
            'scopes' => $character_data->Scopes,
	        'disabled' => 0
        ]);

        return $update_or_create_token;
    }

    public function user()
    {
        return $this->belongsTo('EVEMail\User', 'character_id');
    }
}
