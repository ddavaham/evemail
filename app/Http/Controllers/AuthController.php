<?php

namespace EVEMail\Http\Controllers;

use DB;
use Curl\Curl;
use EVEMail\User;
use EVEMail\Token;
use EVEMail\HttpLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $loginRoute = "/login";
	protected $logoutRoute = "/logout";

    public function __construct (Request $request)
    {
        $this->middleware('guest', ['except' => 'logout']);
        $this->request = $request;
    }
    public function index()
    {
        return view ("pages.sso",[
           'ssoUrl' => config('services.eve.oauth_url')."/oauth/authorize?response_type=code&redirect_uri=".config("services.eve.callback_url")."&client_id=".config("services.eve.client_id")."&scope=".config("services.eve.client_scopes")
        ]);
    }

    public function logout ()
    {
        Auth::logout($this->request->user());
        return redirect()->route('login');
    }

    public function callback ()
    {
        $eve = new EVEController();
        $oauth_verify_auth_code = $eve->oauth_verify_auth_code($this->request->get('code'));

        if ($oauth_verify_auth_code->httpStatusCode != 200) {
            $this->request->session()->flash('alert', [
                "header" => "SSO Error",
                'message' => "Authorization with CCP SSO Failed. Please try again. If errors persists, contact David Davaham. These errors have been logged.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('login');
        }
        $oauth_verify_access_token = $eve->oauth_verify_access_token($oauth_verify_auth_code->response->access_token);
        if ($oauth_verify_access_token->httpStatusCode != 200) {
            $this->request->session()->flash('alert', [
                "header" => "SSO Error",
                'message' => "Unable to Verify Authorization Code from CCP. Please try again. If error persists, contact David Davaham. There errors have been logged.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('login');
        }
        $update_token_data = Token::update_token($oauth_verify_access_token->response, $oauth_verify_auth_code->response);
        if (!$update_token_data) {
            $this->request->session()->flash('alert', [
                "header" => "Token Update Failed",
                'message' => "Unable to update database with Access Tokens. Please try again. If error persists, contact David Davaham.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('login');
        }

        $esi_character_data = $eve->get_character_data($oauth_verify_access_token->response->CharacterID);
        if ($esi_character_data->httpStatusCode !== 200) {
            $this->request->session()->flash('alert', [
                "header" => "Unable to Verify Character Details",
                'message' => "Unable to verify character details with CCP ESI API at this time. Please try again. If error persists, contact David Davaham.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('login');
        }
        $updateOrCreate = User::updateOrCreate([
            'character_id' => $oauth_verify_access_token->response->CharacterID
        ],[
            'character_id' => $oauth_verify_access_token->response->CharacterID,
            'character_name' => $esi_character_data->response->name,
            'corporation_id' => $esi_character_data->response->corporation_id,
            'alliance_id' => (isset($esi_character_data->response->alliance_id)) ? $esi_character_data->response->alliance_id : null
        ]);
        $updateOrCreate->save();
        $user = User::find($oauth_verify_access_token->response->CharacterID);

        Auth::login($user);
        if ($user->is_new) {
            return redirect()->route('dashboard.welcome');
        }
        return redirect()->route('dashboard.fetch');
    }
}
