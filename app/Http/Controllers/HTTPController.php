<?php

namespace EVEMail\Http\Controllers;


use Curl\Curl;
use EVEMail\User;

use EVEMail\Token;
use Carbon\Carbon;
use EVEMail\HttpLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HTTPController extends Controller
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


    public function http_logger($request_id, $data, $requested_data)
    {
        if ($data->httpStatusCode >= 300) {
            $requested_data = json_encode($requested_data);
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
                'requested_data' => $requested_data,
                'response' => json_encode((array)$data->response,true)
            ]);
        }
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

        $this->http_logger(0, $curl_request, [
            'grant_type' => "authorization_code",
            'code' => $code
        ]);
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
        $this->http_logger(0, $curl_request, ['access_token' => $access_token]);
        return $curl_request;
    }

    public function post_refresh_token (Token $token)
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
        $this->http_logger($token->character_id, $curl_request, [
            'grant_type' => "refresh_token",
            'refresh_token' => $token->refresh_token
        ]);
        return $curl_request;
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
        $this->http_logger(Carbon::now()->timestamp, $curl_request, [
            'search' => $search_string,
            'categories' => 'character'
        ]);
        if (count($curl_request->response->character) > 0) {
            $result = $this->post_universe_names($curl_request->response->character);
            return $result;
        }
        return false;
    }
    public function post_universe_names($ids)
    {
        if (!is_array($ids) || count($ids) <= 0) { return false; }
        $curl_request = $this->curl_request([
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'post', config('services.eve.esi_url')."/v2/universe/names/", json_encode($ids), 200);
        $this->http_logger(Carbon::now()->timestamp, $curl_request, ['ids' => $ids]);
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
        $this->http_logger($character_id, $curl_request, null);
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
        $this->http_logger($token->character_id, $curl_request, null);
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
        $this->http_logger($token->character_id, $curl_request, null);
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
        $this->http_logger($token->character_id, $curl_request, null);
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
        $this->http_logger($token->character_id, $curl_request, null);
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
        $this->http_logger($token->character_id, $curl_request, array_merge($data, [
            'datasource' => 'tranquility'
        ]));
        return $curl_request;
    }

    public function delete_mail_header(Token $token, $mail_id)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'delete', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/mail/{$mail_id}/", null, 204);
        $this->http_logger($token->character_id, $curl_request, null);
        return $curl_request;
    }

    public function post_character_mail ($token, $payload)
    {
        $curl_request = $this->curl_request([
            ['key' => "Authorization",'value' => "Bearer ". $token->access_token],
            ['key' => "Content-Type",'value' => "application/json"],
            ['key' => "User-Agent", 'value' => config('services.eve.user_agent')]
        ], 'post', config('services.eve.esi_url')."/v1/characters/{$token->character_id}/mail/", json_encode($payload), 201);
        $this->http_logger($token->character_id, $curl_request, $payload);
        return $curl_request;
    }
}
