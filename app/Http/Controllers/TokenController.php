<?php

namespace EVEMail\Http\Controllers;

use Carbon\Carbon;
use EVEMail\Token;
use EVEMail\Http\Controllers\HTTPController;

class TokenController extends Controller
{
	public $http;

	public function __construct ()
	{
		$this->http = new HTTPController();
	}

	public function get_token($character_id)
	{
		$token = Token::where('character_id', $character_id);
		$current_token = $token->first();
        if (Carbon::now()->toDateTimeString() > $current_token->token_expiry && !$current_token->disabled) {
			$new_token = $this->http->post_refresh_token($current_token);
			if ($new_token->httpStatusCode == 200) {
				$token->update([
					'access_token' => $new_token->response->access_token,
					'refresh_token' => $new_token->response->refresh_token,
					'token_expiry' => Carbon::now()->addMinutes(19)->toDateTimeString(),
					'disabled' => 0
				]);
			} else {
				$token->update([
					'disabled' => 1
				]);
			}
        }
		return $token->first();
	}

	public function disable_token($character_id)
	{
		$token = Token::where('character_id', $character_id)->update([
			"disabled" => 1
		]);
		return $token;
	}
}
