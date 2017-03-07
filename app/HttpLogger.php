<?php

namespace EVEMail;

use Illuminate\Database\Eloquent\Model;

class HttpLogger extends Model
{
    protected $table = 'http_logger';
    protected $fillable = [
		'request_id','error','errorCode','errorMessage','curlError','curlErrorCode','curlErrorMessage','httpError','httpStatusCode',
		'httpErrorMessage','baseUrl','url', 'response', 'type', 'requested_data'
	];
}
