<?php

namespace EVEMail\Jobs;

use EVEMail\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use EVEMail\Http\Controllers\MailController;

class GetCharacterMailHeaders implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $token, $mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Token $token)
    {
        $this->mail = new MailController();
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->mail->get_character_mail_headers($this->token);
        $this->mail->process_queue();
    }
}
