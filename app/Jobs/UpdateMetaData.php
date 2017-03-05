<?php

namespace EVEMail\Jobs;

use Carbon\Carbon;
use EVEMail\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use EVEMail\Http\Controllers\HTTPController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdateMetaData implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $token, $data, $http, $mail_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Token $token, $mail_id, $data)
    {
        $this->http = new HTTPController();
        $this->mail_id = $mail_id;
        $this->data = $data;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->http->update_mail_header($this->token, $this->mail_id, $this->data);
    }

    public function __destruct(){
        foreach (get_class_vars(__CLASS__) as $clsVar => $_) {
            unset($this->$clsVar);
        }
    }
}
