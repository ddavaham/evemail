<?php

namespace EVEMail\Mail;

use EVEMail\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewMailNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $mail_headers;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $mail_headers)
    {
        $this->user = $user;
        $this->mail_headers = $mail_headers;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('notifications@reply.evemail.space', "EVEMail Notifications")->subject('EVEMail Alert: You have new EVEMails')->view('email.new_mail');
    }
}
