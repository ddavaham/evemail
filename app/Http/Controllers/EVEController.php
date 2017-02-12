<?php

namespace EVEMail\Http\Controllers;


use Curl\Curl;
use EVEMail\User;

use EVEMail\Token;
use Carbon\Carbon;
use EVEMail\HttpLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EVEController extends Controller
{
    public function __construct ()
    {
        $this->request = new Request();
    }

    public function curl_request($headers, $method, $path, $data, $returnCode = 200, $loopCounter = 5)
    {
        for($x=0;$x<=$loopCounter;$x++)
        {
            $curl = new Curl();
            foreach ($headers as $header)
            {

                if (isset($header['key']) && $header['key'] !== null && isset($header['value']) && $header['value'] !== null) {
                    $curl->setHeader($header['key'], $header['value']);
                }
            }

            if ($method === "get") {
                $curl->get($path, $data);
            } else if ($method === "post") {
                $curl->post($path, $data);
            } else if ($method === "put") {
                $curl->put($path, $data);
            } else if ($method === "delete") {
                $curl->delete($path);
            }
            if ($curl->httpStatusCode == $returnCode) {
                break 1;
            }
        }

        return $curl;
        $curl->close();

    }


    public function http_logger($request_id, $data)
    {
        HttpLogger::create([
            'request_id' => $request_id,
            'error' => $data->error,
            'errorCode' => $data->errorCode,
            'errorMessage' => $data->errorMessage,
            'curlError' => $data->curlError,
            'curlErrorCode' => $data->curlErrorCode,
            'curlErrorMessage' => $data->curlErrorMessage,
            'httpError' => $data->httpError,
            'httpStatusCode' => $data->httpStatusCode,
            'httpErrorMessage' => $data->httpErrorMessage,
            'baseUrl' => $data->baseUrl,
            'url' => $data->url,
            'requestHeaders' => json_encode((array)$data->requestHeaders, true),
            'responseHeaders' => json_encode((array)$data->responseHeaders,true),
            'response' => json_encode((array)$data->response,true)
        ]);
    }

    public function oauth_verify_auth_code ($code)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => base64_encode(config("services.eve.client_id").":".config("services.eve.client_secret"))],
            ['key' => "Content-Type",'value' => "application/x-www-form-urlencoded"],
            ['key' => "Host",'value' => "login.eveonline.com"]
        ], 'post',config('services.eve.oauth_url')."/oauth/token", [
            'grant_type' => "authorization_code",
            'code' => $code
        ]);

        $this->http_logger(0, $curl_request);
        return $curl_request;
    }

    public function oauth_verify_access_token($access_token)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ".$access_token],
            ['key' => "Content-Type",'value' => "application/x-www-form-urlencoded"],
            ['key' => "Host",'value' => "login.eveonline.com"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'get', config('services.eve.oauth_url')."/oauth/verify", []);
        $this->http_logger(0, $curl_request);
        return $curl_request;
    }

    public function refresh_token (Token $token)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => base64_encode(config("services.eve.client_id").":".config("services.eve.client_secret"))],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "Host",'value' => "login.eveonline.com"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'post', config('services.eve.oauth_url')."/oauth/token", [
            'grant_type' => "refresh_token",
            'refresh_token' => $token->refresh_token
        ]);
        $this->http_logger($token->character_id, $curl_request);
        if ($curl_request->error) {
            return false;
        }
        $token->access_token = $curl_request->response->access_token;
        $token->refresh_token = $curl_request->response->refresh_token;
        $token->token_expiry = Carbon::now()->addMinutes(20)->toDateTimeString();
        $token->save();

        return $token;
    }
    public function get_search($search_string)
    {
        $curl_request = $this->curl_request([
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'get', config('services.eve.esi_url')."/v1/search/", [
            'search' => $search_string,
            'categories' => 'character'

        ], 200, 5);
        $this->http_logger(Carbon::now()->timestamp, $curl_request);
        if (count($curl_request->response->character) > 1) {
            $result = $this->post_universe_names($curl_request->response->character);
            return $result;
        }
        return false;
    }
    public function post_universe_names($ids)
    {
        if (!is_array($ids)) { return false; }
        $curl_request = $this->curl_request([
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'post', config('services.eve.esi_url')."/v2/universe/names/", json_encode($ids), 200);
        $this->http_logger(Carbon::now()->timestamp, $curl_request);
        return $curl_request;
    }



    public function get_character_data($character_id, $loopCounter = 5)
    {
        $curl_request = $this->curl_request([
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'get', config('services.eve.esi_url')."/v4/characters/{$character_id}/", [
            'datasource' => 'tranquility'
        ], 200, $loopCounter);
        $this->http_logger($character_id, $curl_request);
        return $curl_request;
    }

    public function get_character_mail_headers (Token $token)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'get', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/mail/", [
            'datasource' => 'tranquility'
        ]);
        $this->http_logger($token->character_id, $curl_request);
        return $curl_request;
    }

    public function get_character_mail_labels (Token $token)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'get', config('services.eve.esi_url')."/v3/characters/{$token->character_id}/mail/labels/", [
            'datasource' => 'tranquility'
        ]);
        $this->http_logger($token->character_id, $curl_request);
        return $curl_request;
    }

    public function get_character_mailing_lists (Token $token)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'get', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/mail/lists/", [
            'datasource' => 'tranquility'
        ]);
        $this->http_logger($token->character_id, $curl_request);
        return $curl_request;
    }

    public function get_character_mail_body (Token $token, $mail_id)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'get', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/mail/{$mail_id}/", [
            'datasource' => 'tranquility'
        ]);
        $this->http_logger($token->character_id, $curl_request);
        return $curl_request;
    }

    public function update_mail_header(Token $token, $mail_id, $data)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'put', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/mail/{$mail_id}/", json_encode(array_merge($data, [
            'datasource' => 'tranquility'
        ]), JSON_FORCE_OBJECT), 204);
        $this->http_logger($token->character_id, $curl_request);
        return $curl_request;
    }

    public function delete_mail_header(Token $token, $mail_id)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'delete', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/mail/{$mail_id}/", null, 204);
        $this->http_logger($token->character_id, $curl_request);
        return $curl_request;
    }

    public function post_character_mail ($token, $payload)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'post', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/mail/", json_encode($payload), 201);
        $this->http_logger($token->character_id, $curl_request);
        // if ($curl_request->httpStatusCode ==201){
        //     Log::info("Mail Sent Successfully. New Mail {$curl_request->response} generated");
        // }
        return $curl_request;
    }

    public function get_character_contacts (Token $token)
    {
        $character_contacts = [];
        for($page=1;$page<10;$page++)
        {

            $curl_request = $this->curl_request([
                ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
                ['key' => "Content-Type",'value' => "application/json"],
                ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
            ], 'get', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/contacts/", [
                'page' => $page,
                'datasource' => 'tranquility'
            ], 200, 5);

            $this->http_logger($token->character_id, $curl_request);
            if ($curl_request->httpStatusCode == 200) {

                if (!empty($curl_request->response)) {

                    foreach ((array)$curl_request->response as $contact) {
                        $character_contacts[] = $contact;
                        $this->http_logger($token->character_id, $curl_request);
                    }
                } else {
                    break 1;
                }
            }
            usleep(500);
        }
        return [
            'curl' => $curl_request,
            'contacts' => $character_contacts
        ];
    }




}
