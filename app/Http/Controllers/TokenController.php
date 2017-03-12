<?php

namespace EVEMail\Http\Controllers;

use Carbon\Carbon;
use EVEMail\Token;
use EVEMail\Http\Controllers\HTTPController;

class TokenController extends Controller
{
	public $http, $request;

	public function __construct ()
	{
		$this->http = new HTTPController();
	}

	public function get_token($character_id)
	{
		$token = Token::where('character_id', $character_id)->first();

        if (Carbon::now()->toDateTimeString() > $token->token_expiry) {
			$new_token = $this->http->post_refresh_token($token);
			if ($new_token->httpStatusCode == 200) {
				$token->access_token = $new_token->response->access_token;
				$token->refresh_token = $new_token->response->refresh_token;
				$token->token_expiry = Carbon::now()->addMinutes(19)->toDateTimeString();
				$token->disabled = 0;
			} else {
				$token->disabled = 1;
			}
			$token->save();

        }
		return $token;
	}
}
