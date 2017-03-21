<?php

namespace EVEMail\Jobs;

use Curl\Curl;
use EVEMail\Token;
use EVEMail\MailBody;
use EVEMail\MailHeader;
use EVEMail\Http\Controllers\HTTPController;
use EVEMail\Http\Controllers\TokenController;
use EVEMail\Http\Controllers\MailController;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteMailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $mail_id, $character_id, $token, $http, $mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($character_id, $mail_id)
    {
        $this->mail_id = $mail_id;
        $this->character_id = $character_id;
        $this->token = new TokenController();
        $this->http = new HTTPController();
        $this->mail = new MailController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $token = $this->token->get_token($this->character_id);
        if ($token->disabled) {
            return false;
        }
        $delete_mail_header = $this->http->delete_mail_header($token, $this->mail_id);
        if ($delete_mail_header instanceof Curl) {
            if ($delete_mail_header->httpStatusCode == 204) {
                MailHeader::where(['character_id' => $token->character_id, 'mail_id' => $this->mail_id])->delete();
                MailBody::where(['character_id' => $token->character_id, 'mail_id' => $this->mail_id])->delete();

                return true;
            }
        }
        return false;
    }

}
