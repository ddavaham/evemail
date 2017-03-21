<?php

namespace EVEMail\Console\Commands;

use EVEMail\User;
use EVEMail\Token;
use EVEMail\MailHeader;
use EVEMail\MailHeaderUpdate;
use EVEMail\MailRecipient;
use EVEMail\MailLabel;
use Illuminate\Console\Command;

class PurgeNewAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::where([
            ['users.created_at', '<', Carbon::now()->subHours(1)],
            ['users.is_new','=', 1]
        ])->get();
        foreach ($users as $user) {
            $check_for_headers = MailHeader::where('character_id', $user->character_id)->get();
            if ($check_for_headers->count() > 0) {
                //MailHeader::where('character_id', $user->character_id)->delete();
            }
            $cheak_for_mailing_lists = MailRecipient::where(['character_id' => $user->character_id, 'recipient_type' => "mailing_list"])->get();
            if ($cheak_for_mailing_lists->count() > 0) {
                //MailRecipient::where(['character_id' => $user->character_id, 'recipient_type' => "mailing_list"])->delete();
            }
            $cheak_for_mail_labels = MailLabel::where('character_id', $user->character_id)->get();
            if ($cheak_for_mail_labels->count() > 0) {
                //MailLabel::where('character_id', $user->character_id)->delete();
            }
            $check_for_header_update = MailHeaderUpdate::where('character_id', $user->character_id)->first();
            if (!is_null($check_for_header_update)) {
                //MailHeaderUpdate::where('character_id', $user->character_id)->delete();
            }
            $check_for_token = Token::where('character_id', $user->character_id)->first();
            if (!is_null($check_for_header_update)) {
                //Token::where('character_id', $user->character_id)->delete();
            }
            User::where('character_id', $user->character_id)->delete();
        }

    }
}
