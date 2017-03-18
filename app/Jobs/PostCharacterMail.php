<?php

namespace EVEMail\Jobs;

use Carbon\Carbon;
use EVEMail\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use EVEMail\Http\Controllers\HTTPController;
use EVEMail\Http\Controllers\MailController;
use EVEMail\Http\Controllers\TokenController;



class PostCharacterMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $character_id, $payload, $http, $mail, $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($character_id, $payload)
    {
        $this->http = new HTTPController();
        $this->mail = new MailController();
        $this->payload = $payload;
        $this->character_id = $character_id;
        $this->token = new TokenController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $get_token = $this->token->get_token($this->character_id);
        if (!$get_token->disabled) {
            $this->http->post_character_mail($get_token, $this->payload);
            $this->mail->get_character_mail_headers((int)$this->character_id);
            $this->mail->process_queue();
        }

    }

    public function __destruct(){
        foreach (get_class_vars(__CLASS__) as $clsVar => $_) {
            unset($this->$clsVar);
        }
    }

}
