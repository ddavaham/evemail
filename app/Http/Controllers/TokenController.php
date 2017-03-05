<?php

namespace EVEMail\Http\Controllers;

use Carbon\Carbon;
use EVEMail\Token;
use EVEMail\Http\Controllers\EVEController;
use Illuminate\Http\Request;

class TokenController extends Controller
{
	public $eve;

	public function __construct ()
	{
		$this->eve = new EVEController();
	}

	public function update_token(Token $token)
	{
		if ($token->disabled) {
			dd(1);
			return null;
        }
		$new_token = $this->eve->post_refresh_token($token);
		dd($new_token);
        if (Carbon::now()->toDateTimeString() > $token->token_expiry) {

        }
		dd(3);
        return $token;
	}
}
