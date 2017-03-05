<?php

namespace EVEMail\Http\Controllers;

use Carbon\Carbon;
use EVEMail\Token;
use EVEMail\Http\Controllers\HTTPController;
use Illuminate\Http\Request;

class TokenController extends Controller
{
	public $http;

	public function __construct ()
	{
		$this->http = new HTTPController();
	}

	public function update_token(Token $token)
	{
		if ($token->disabled) {
			return false;
        }

        if (Carbon::now()->toDateTimeString() > $token->token_expiry) {
			$new_token = $this->http->post_refresh_token($token);
			if ($new_token->httpStatusCode == 200) {
				$token->access_token = $new_token->response->access_token;
				$token->refresh_token = $new_token->response->refresh_token;
				$token->token_expiry = Carbon::now()->addMinutes(19)->toDateTimeString();
				$token->disabled = 0;
				$token->save();
				return $token;
			} else {
				$token->disabled = 1;
				$token->save();
				return false;
			}

        }
		return $token;
	}
}
