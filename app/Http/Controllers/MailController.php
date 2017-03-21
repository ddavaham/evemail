<?php
namespace EVEMail\Http\Controllers;

use DB;
use Mail;
use Curl\Curl;
use EVEMail\User;
use Carbon\Carbon;
use EVEMail\Token;
use EVEMail\Queue;
use EVEMail\MailBody;
use EVEMail\MailLabel;
use EVEMail\MailHeader;
use EVEMail\MailRecipient;
use EVEMail\CharacterContact;
use EVEMail\MailHeaderUpdate;
use EVEMail\Jobs\ProcessQueue;
use EVEMail\Jobs\DeleteMailJob;
use EVEMail\Jobs\UpdateMetaData;
use EVEMail\Jobs\PostCharacterMail;
use Illuminate\Support\Facades\Auth;
use EVEMail\Mail\NewMailNotification;
use EVEMail\Http\Controllers\HTTPController;
use EVEMail\Http\Controllers\TokenController;


class MailController extends Controller
{
    private $token, $http;

    public function __construct ()
    {
        $this->http = new HTTPController();
        $this->token = new TokenController();

    }

    public function process_queue()
    {
        $queue = Queue::get();
        if ($queue->count() > 0) {
            $job = (new \EVEMail\Jobs\ProcessQueue())
                    ->delay(Carbon::now()->addSeconds(5));
            dispatch($job);
        }
    }

    public function get_character_mail_labels ($character_id)
    {
        $token = $this->token->get_token($character_id);
        if ($token instanceof Token) {
            if ($token->disabled)
            {
                return $token;
            }
        }
        $mail_labels = $this->http->get_character_mail_labels($token);

        if ($mail_labels instanceof Curl)
        {
            if ($mail_labels->httpStatusCode == 200)
            {
                foreach ($mail_labels->response->labels as $label) {
                    $exists = MailLabel::where(['character_id' => $token->character_id, 'label_id' => $label->label_id])->first();

                    if (!is_null($exists)) {
                        MailLabel::where(['character_id' => $token->character_id, 'label_id' => $label->label_id])->update([
                            'label_name' => $label->name,
                            'label_unread_count' => (isset($label->unread_count)) ? $label->unread_count : null
                        ]);
                    } else {
                        MailLabel::create([
                            'character_id' => $token->character_id,
                            'label_id' => $label->label_id,
                            'label_name' => $label->name,
                            'label_unread_count' => (isset($label->unread_count)) ? $label->unread_count : null
                        ]);
                    }
                }
            }
            if ($mail_labels->httpStatusCode >= 400 && $mail_labels->httpStatusCode < 500) {
                $this->token->disable_token($character_id);
                return $this->token->get_token();
            }
            return $mail_labels;
        }
    }
    public function get_character_mailing_lists ($character_id)
    {
        $token = $this->token->get_token($character_id);
        if ($token instanceof Token) {
            if ($token->disabled)
            {
                return $token;
            }
        }
        $mailing_lists = $this->http->get_character_mailing_lists($token);

        if ($mailing_lists instanceof Curl)
        {
            if ($mailing_lists->httpStatusCode == 200)
            {

                foreach ($mailing_lists->response as $mailing_list) {

                    $mailing_list_known = MailRecipient::where(['recipient_id' => $mailing_list->mailing_list_id, 'recipient_type' => "mailing_list"])->first();
                    if (is_null($mailing_list_known))
                    {
                        MailRecipient::create([
                            'character_id' => $character_id,
                            'recipient_id' => $mailing_list->mailing_list_id,
                            'recipient_name' => $mailing_list->name,
                            'recipient_type' => "mailing_list",
                            'inactive' => 0
                        ]);
                    } else {
                        if ($mailing_list_known->character_id !== $character_id) {
                            MailRecipient::where('recipient_id', $mailing_list->mailing_list_id)->update([
                                'recipient_name' => $mailing_list->name
                            ]);
                        }
                        // MailRecipient::where(['recipient_id' => $mailing_list->mailing_list_id, 'character_id' => $character_id])->update([
                        //     'inactive' => 1
                        // ]);
                    }
                }
            }
            if ($mailing_lists->httpStatusCode >= 400 && $mailing_lists->httpStatusCode < 500) {
                $this->token->disable_token($character_id);
                return $this->token->get_token();
            }
            return $mailing_lists;
        }
    }

    public function get_character_mail_headers ($character_id)
    {
        $token = $this->token->get_token($character_id);
        if ($token instanceof Token) {
            if ($token->disabled)
            {
                return $token;
            }
        }
        $mail_headers = $this->http->get_character_mail_headers($token);

        if ($mail_headers instanceof Curl)
        {
            if ($mail_headers->httpStatusCode == 200)
            {
                foreach ($mail_headers->response as $mail_header) {
                    foreach ($mail_header->recipients as $mail_recipient) {
                        $recipient_known = MailRecipient::where('recipient_id', $mail_recipient->recipient_id)->first();
                        if (is_null($recipient_known)) {
                            if ($mail_recipient->recipient_type === "mailing_list") {
                                MailRecipient::create([
                                    'character_id' => $character_id,
                                    'recipient_id' => $mail_recipient->recipient_id,
                                    'recipient_name' => "Unknown Mailing List",
                                    'recipient_type' => "mailing_list"
                                ]);
                            } else {
                                $is_queued = Queue::where('queue_id', $mail_recipient->recipient_id)->first();
                                if (is_null($is_queued)) {
                                    $PlaceInQueue = Queue::create([
                                        'queue_id' => $mail_recipient->recipient_id,
                                        'location' => 'MailHeaderRecipientLoop'
                                    ]);
                                }
                            }
                        }
                    }
                    if ($mail_header->from >= 145000000 && $mail_header->from <= 145000000) {
                        $sender_known = MailRecipient::where('recipient_id', $mail_header->from)->first();
                        if (is_null($sender_known)) {
                            MailRecipient::create([
                                'character_id' => $character_id,
                                'recipient_id' => $mail_header->from,
                                'recipient_name' => "Unknown Mailing List",
                                'recipient_type' => "mailing_list"
                            ]);
                        }
                    } else {
                        $sender_known = MailRecipient::where('recipient_id', $mail_header->from)->first();
                        if (is_null($sender_known)) {
                            $is_queued = Queue::where('queue_id', $mail_header->from)->first();
                            if (is_null($is_queued)) {
                                $PlaceInQueue = Queue::create([
                                    'queue_id' => $mail_header->from,
                                    'location' => "MailHeaderSenderKnown"
                                ]);
                            }
                        }
                    }
                    $header = MailHeader::where(['character_id' => $character_id, 'mail_id' => $mail_header->mail_id])->first();
                    if (is_null($header)) {
                        MailHeader::create([
                            'character_id' => $character_id,
                            'mail_id' => $mail_header->mail_id,
                            'mail_subject' => $mail_header->subject,
                            'mail_sender' => $mail_header->from,
                            'mail_sent_date' => Carbon::createFromTimestamp(strtotime($mail_header->timestamp))->toDateTimeString(),
                            'mail_labels' => implode(',',$mail_header->labels),
                            'mail_recipient' => json_encode($mail_header->recipients),
                            'is_read' => $mail_header->is_read,
                            'raw_json' => json_encode($mail_header)
                        ]);
                    } else {
                        MailHeader::where(['character_id' => $character_id, 'mail_id' => $mail_header->mail_id])->update([
                            'mail_sent_date' => Carbon::createFromTimestamp(strtotime($mail_header->timestamp))->toDateTimeString(),
                            'mail_labels' => implode(',',$mail_header->labels),
                            'is_read' => $mail_header->is_read,
                            'raw_json' => json_encode($mail_header)
                        ]);
                    }

                }
                $header_update = MailHeaderUpdate::where('character_id', $character_id)->first();
                if (is_null($header_update)) {
                    MailHeaderUpdate::create([
                        'character_id' => $character_id,
                        'last_header_update' => Carbon::now()->toDateTimeString()
                    ]);
                } else {
                    MailHeaderUpdate::where('character_id', $character_id)->update([
                        'last_header_update' => Carbon::now()->toDateTimeString()
                    ]);
                }
            }
            if ($mail_headers->httpStatusCode >= 400 && $mail_headers->httpStatusCode < 500) {
                $this->token->disable_token($character_id);
                return $this->token->get_token($character_id);
            }
            return $mail_headers;
        }

    }

    public function id_search($search_string)
    {
        $get_search = $this->http->get_search($search_string);
        if ($get_search instanceof Curl) {
            if ($get_search && $get_search->httpStatusCode == 200) {
                foreach ($get_search->response as $response) {
                    $firstOrCreate = MailRecipient::firstOrCreate([
                        'recipient_id' => $response->id,
                        'recipient_name' => $response->name,
                        'recipient_type' => $response->category
                    ]);
                }
            }
            return $get_search;
        }

    }

    public function get_mail_body ($character_id, $mail_id)
    {
        $mail_header = MailHeader::where(['character_id' => $character_id, 'mail_id' => $mail_id])->first();
        $token = $this->token->get_token($character_id);
        if ($token->disabled) {
            return false;
        }
        $mail_body = $this->http->get_character_mail_body($token, $mail_id);
        if ($mail_body->httpStatusCode == 200) {


            // link to kms
            $formattedMessage = preg_replace('/<a href="killReport:(\d+):(\w+)">/', '<a href="https://beta.eve-kill.net/kill/\1/#\2" target="_blank">', $mail_body->response->body);
            // link fits
            $formattedMessage = preg_replace('/<a href="fitting:([\d:;]+)">/', '<a href="https://o.smium.org/loadout/dna/\1" target="_blank">', $formattedMessage);
            // link system/station
            $formattedMessage = preg_replace('/<a href="showinfo:(?:5|3867)\/\/(\d+)">/', '<a href="https://evemaps.dotlan.net/search?q=\1">', $formattedMessage);
            // link char
            $formattedMessage = preg_replace('/<a href="showinfo:(?:1377|1378)\/\/(\d+)">/', '<a href="https://beta.eve-kill.net/character/\1">', $formattedMessage);
            // link corp
            $formattedMessage = preg_replace('/<a href="showinfo:2\/\/(\d+)">/', '<a href="https://beta.eve-kill.net/corporation/\1">', $formattedMessage);
            // link alliance
            $formattedMessage = preg_replace('/<a href="showinfo:16159\/\/(\d+)">/', '<a href="https://beta.eve-kill.net/alliance/\1">', $formattedMessage);
            // remove font size
            $formattedMessage = preg_replace('/size="[^"]*[^"]"/', "", $formattedMessage);
            // remove wrong color
            $formattedMessage = preg_replace('/(color="#)[a-f0-9]{2}([a-f0-9]{6}")/', '\1\2', $formattedMessage);

            MailBody::create([
                'mail_id' => $mail_id,
                'character_id' => $mail_header->character_id,
                'mail_body' => $formattedMessage
            ]);
            return true;
        }
        return false;
    }

    public function send_message($character_id, $message_payload, Carbon $delay=null)
    {
        $token = $this->token->get_token($character_id);
        if ($token->disabled) {
            return $token;
        }
        $job = (new PostCharacterMail($character_id, $message_payload));
        if (!is_null($delay)) {
            $job->delay($delay);
        }
        dispatch($job);
        return true;

    }

    public function mark_mail_read($character_id, $mail_id, Carbon $delay=null)
    {
        $token = $this->token->get_token($character_id);
        if ($token->disabled) {
            return false;
        }
        MailHeader::where(['character_id' => $character_id, 'mail_id' => $mail_id])->update([
            'is_read'=> 1
        ]);
        $data = [
            "read" => true
        ];
        $job = (new UpdateMetaData($token, $mail_id, $data));
        if (!is_null($delay)) {
            $job->delay($delay);
        }
        dispatch($job);
        return true;
    }

    public function mark_mail_unread($character_id, $mail_id, Carbon $delay=null)
    {
        $token = $this->token->get_token($character_id);
        if ($token->disabled) {
            return false;
        }
        MailHeader::where(['character_id' => $character_id, 'mail_id' => $mail_id])->update([
            'is_read'=> 0
        ]);
        $data = [
            "read" => false
        ];
        $job = (new UpdateMetaData($token, $mail_id, $data));
        if (!is_null($delay)) {
            $job->delay($delay);
        }
        dispatch($job);
        return true;
    }

    public function delete_mail ($character_id, $mail_id, Carbon $delay=null)
    {
        $token = $this->token->get_token($character_id);
        if ($token->disabled) {
            return $token;
        }
        $job = (new DeleteMailJob($character_id, $mail_id));
        if (!is_null($delay)) {
            $job->delay($delay);
        }
        dispatch($job);
        return true;
    }

    public function check_for_unknown_headers ($character_id) {
        $user = User::findOrFail($character_id);

        if (!isset($user->preferences()['new_mail_notifications']) || $user->preferences()['new_mail_notifications'] == 0) {
            return false;
        }
        if (is_null($user->email()->first())) {
            $user->email()->delete();
            $user->update([
                'preferences' => null
            ]);
            return false;
        }

        $mail_headers = MailHeader::where([
            'character_id' => $user->character_id,
            'is_known' => 0,
            'is_read' => 0
        ])->orderby('created_at', 'desc');
        $get_mail_headers = $mail_headers->get();

        if ($get_mail_headers->count() > 0 ) {
            foreach ($get_mail_headers as $k=>$header) {
                $label_ids = explode(',',$header->mail_labels);
                foreach ($label_ids as $label_id) {
                    if ($label_id == 2) {
                        unset($get_mail_headers[$k]);
                    }
                }
            }
            Mail::to($user->email()->first()->character_email)->send(new NewMailNotification($user, $get_mail_headers));
            $mail_headers->update([
                'is_known' => 1
            ]);
        }
    }
}
