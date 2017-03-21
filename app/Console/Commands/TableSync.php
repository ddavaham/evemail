<?php

namespace EVEMail\Console\Commands;

use EVEMail\User;
use EVEMail\Token;
use EVEMail\UserEmail;
use Carbon\Carbon;
use EVEMail\MailHeader;
use EVEMail\MailHeaderUpdate;
use EVEMail\MailRecipient;
use EVEMail\MailLabel;

use Illuminate\Console\Command;

class TableSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:table_sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Users, Token, & Mail Header Update Table';

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
        $users = User::get();
        foreach ($users as $user) {
            $check_for_token = Token::where('character_id', $user->character_id)->first();
            $check_for_mail_header_update = MailHeaderUpdate::where('character_id', $user->character_id)->first();
            if (is_null($check_for_token)) {
                MailHeader::where('character_id', $user->character_id)->delete();
                MailRecipient::where(['character_id' => $user->character_id, 'recipient_type' => "mailing_list"])->delete();
                MailLabel::where('character_id', $user->character_id)->delete();
                MailHeaderUpdate::where('character_id', $user->character_id)->delete();
                UserEmail::where('character_id', $user->character_id)->delete();
                User::where('character_id', $user->character_id)->delete();
            }
            if (is_null($check_for_mail_header_update)) {
                MailHeaderUpdate::create([
                    'character_id' => $user->character_id,
                    'last_header_update' => Carbon::now()->toDateTimeString()
                ]);
            }
        }
        $tokens = Token::get();
        foreach ($tokens as $token) {
            $check_for_user = User::where('character_id', $token->character_id)->first();
            $check_for_mail_header_update = MailHeaderUpdate::where('character_id', $token->character_id)->first();
            if (is_null($check_for_user)) {
                MailHeader::where('character_id', $token->character_id)->delete();
                MailRecipient::where(['character_id' => $token->character_id, 'recipient_type' => "mailing_list"])->delete();
                MailLabel::where('character_id', $token->character_id)->delete();
                MailHeaderUpdate::where('character_id', $token->character_id)->delete();
                UserEmail::where('character_id', $token->character_id)->delete();
                Token::where('character_id', $token->character_id)->delete();
            }
            if (is_null($check_for_mail_header_update)) {
                MailHeaderUpdate::create([
                    'character_id' => $token->character_id,
                    'last_header_update' => Carbon::now()->toDateTimeString()
                ]);
            }
        }
        $mail_header_updates = MailHeaderUpdate::get();
        foreach ($mail_header_updates as $mail_header_update) {
            $check_for_user = User::where('character_id', $mail_header_update->character_id)->first();
            $check_for_token = Token::where('character_id', $mail_header_update->character_id)->first();
            if (is_null($check_for_user) || is_null($check_for_token)) {
                MailHeader::where('character_id', $token->character_id)->delete();
                MailRecipient::where(['character_id' => $token->character_id, 'recipient_type' => "mailing_list"])->delete();
                MailLabel::where('character_id', $token->character_id)->delete();
                MailHeaderUpdate::where('character_id', $token->character_id)->delete();
                UserEmail::where('character_id', $token->character_id)->delete();
                Token::where('character_id', $token->character_id)->delete();
                User::where('character_id', $token->character_id)->delete();
            }
        }
    }
}
