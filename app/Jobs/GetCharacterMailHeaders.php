<?php

namespace EVEMail\Jobs;

use EVEMail\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use EVEMail\Http\Controllers\MailController;
use EVEMail\Http\Controllers\TokenController;

class GetCharacterMailHeaders implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $token, $mail, $character_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($character_id)
    {
        $this->mail = new MailController();
        $this->token = new TokenController();
        $this->character_id = $character_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $token = $this->token->update_token(Token::where('character_id', $this->character_id)->first());
        if ($token !== false) {
            $this->mail->get_character_mail_headers($token);
            $this->mail->process_queue();
        }

    }
}
