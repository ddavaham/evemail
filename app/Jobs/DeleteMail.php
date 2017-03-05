<?php

namespace EVEMail\Jobs;

use EVEMail\MailBody;
use EVEMail\MailHeader;
use EVEMail\Token;
use EVEMail\Http\Controller\HTTPController;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Token $token, $mail_id)
    {
        $this->mail_id = $mail_id;
        $this->token = $token;
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
        $mail_body = MailBody::where(['character_id' => $this->token->character_id, 'mail_id' => $mail_id])->first();
        $token = $this->mail->refresh_token(Token::where('character_id', $mail_body->character_id)->first());
        $delete_mail_header = $this->http->delete_mail_header($token, $mail_id);
        MailHeader::where(['character_id' => $token->character_id, 'mail_id' => $mail_id])->delete();
        MailBody::where(['character_id' => $token->character_id, 'mail_id' => $mail_id])->delete();

    }


    public function __destruct(){
        foreach (get_class_vars(__CLASS__) as $clsVar => $_) {
            unset($this->$clsVar);
        }
    }
}
