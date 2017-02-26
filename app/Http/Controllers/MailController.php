<?php
namespace EVEMail\Http\Controllers;

use DB;
use Carbon\Carbon;
use EVEMail\User;
use EVEMail\Token;
use EVEMail\Queue;
use EVEMail\MailHeader;
use EVEMail\MailHeaderUpdate;
use EVEMail\MailLabel;
use EVEMail\MailBody;
use EVEMail\MailRecipient;
use EVEMail\CharacterContact;
use EVEMail\Jobs\ProcessQueue;
use EVEMail\Jobs\UpdateMetaData;
use EVEMail\Jobs\PostCharacterMail;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use EVEMail\Http\Controllers\EVEController;

class MailController extends Controller
{


    private $token, $queueTable;

    public function __construct ()
    {
        $this->eve = new EVEController();
    }

    public function testing ()
    {
        dd("You see nothing");
    }

    public function first_time_download(Request $request)
    {
        (!Auth::user()->is_new) ? redirect()->route('dashboard') : null;

        $token = $this->refresh_token(Token::where('character_id', Auth::user()->character_id)->first());

        $mail_headers = $this->get_character_mail_headers($token);

        $mail_labels = $this->get_character_mail_labels($token);

        $mailing_lists = $this->get_character_mailing_lists ($token);

        //$character_contacts = $this->get_character_contacts($token);

        $process_queue = $this->process_queue();

        if ($mail_headers && $mail_labels && $mailing_lists) {
            User::where('character_id', Auth::user()->character_id)->update([
                'is_new' => 0
            ]);
            $request->session()->flash('alert', [
                "header" => "Mail Downloaded Successfully",
                'message' => "We have downloaded your mails successfully. Bear with us while we continue downloading the names of all the character that are part of those mails. You can access your mails, but until our minions have reached out to CCP to get the character data for those emails, we won't know whose name to display to you. Thanks for using EVEMail.Space",
                'type' => 'success',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $request->session()->flash('alert', [
            "header" => "Houston, We have an problem",
            'message' => "Sorry for this inconvienence {$request->user()->character_name}. We are unable to download your mails at this time. Please try again in a few minutes.",
            'type' => 'danger',
            'close' => 1
        ]);
        return redirect()->route('dashboard.welcome');

    }

    public function process_queue()
    {
        $queued_ids = Queue::select('queue_id')->get();

        if (!is_null($queued_ids)) {
            $ids = [];
            foreach ($queued_ids as $id) {
                $ids[] = $id->queue_id;
            }
            $parse_ids = $this->eve->post_universe_names($ids);

            if ($parse_ids && $parse_ids->httpStatusCode == 200) {
                foreach ($parse_ids->response as $parsed_id) {
                    MailRecipient::create([
                        'recipient_id' => $parsed_id->id,
                        'recipient_name' => $parsed_id->name,
                        'recipient_type' => $parsed_id->category
                    ]);
                    Queue::where('queue_id', $parsed_id->id)->delete();
                }
            }
        }

        // $job = (new ProcessQueue())
        //         ->delay(Carbon::now()->addSeconds(5));
        // dispatch($job);
    }

    public function get_character_mail_labels (Token $token)
    {

        $token = $this->refresh_token($token);
        $mail_labels = $this->eve->get_character_mail_labels($token);

        if ($mail_labels->httpStatusCode != 200) {
            return false;
        } else {
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

            return true;
        }
    }

    public function get_character_mailing_lists(Token $token)
    {
        $token = $this->refresh_token($token);
        $mailing_lists = $this->eve->get_character_mailing_lists ($token);
        if ($mailing_lists->httpStatusCode != 200) {
            return false;
        }
        foreach ($mailing_lists->response as $mailing_list) {
            MailRecipient::firstOrCreate([
                'character_id' => $token->character_id,
                'recipient_id' => $mailing_list->mailing_list_id,
                'recipient_name' => $mailing_list->name
            ]);
        }
        return true;

    }

    // public function get_character_contacts(Token $token)
    // {
    //     $token = $this->refresh_token($token);
    //     $character_contacts = $this->eve->get_character_contacts($token);
    //     if ($character_contacts['curl']->httpStatusCode != 200) {
    //         return false;
    //     } else {
    //         if (isset($character_contacts['contacts'])) {
    //             foreach ($character_contacts['contacts'] as $contact) {
    //
    //                 $contact_known = MailRecipient::where('recipient_id', $contact->contact_id)->first();
    //                 if (is_null($contact_known)) {
    //                     $is_queued = Queue::where('queue_id', $contact->contact_id)->first();
    //                     if (is_null($is_queued)) {
    //                         $PlaceInQueue = Queue::create([
    //                             'queue_id' => $contact->contact_id
    //                         ]);
    //                     }
    //                 }
    //                 $updateOrCreate = CharacterContact::updateOrCreate([
    //                     'character_id' => $token->character_id,
    //                     'contact_id' => $contact->contact_id
    //                 ], [
    //                     'character_id' => $token->character_id,
    //                     'contact_id' => $contact->contact_id,
    //                     'contact_type' => $contact->contact_type
    //                 ]);
    //             }
    //         }
    //         return true;
    //     }
    // }

    public function get_character_mail_headers (Token $token)
    {

        $token = $this->refresh_token($token);

        $mail_headers = $this->eve->get_character_mail_headers($token);
        if ($mail_headers->httpStatusCode != 200) {
            return false;
        }
        foreach ($mail_headers->response as $mail_header) {
            $header_exists = MailHeader::where(['character_id' => $token->character_id, 'mail_id' => $mail_header->mail_id])->first();
            if (is_null($header_exists)) {
                foreach ($mail_header->recipients as $mail_recipient) {
                    if ($mail_recipient->recipient_type !== "mailing_list") {
                        $recipient_known = MailRecipient::where('recipient_id', $mail_recipient->recipient_id)->first();
                        if (is_null($recipient_known)) {
                            $is_queued = Queue::where('queue_id', $mail_recipient->recipient_id)->first();
                            if (is_null($is_queued)) {
                                $PlaceInQueue = Queue::create([
                                    'queue_id' => $mail_recipient->recipient_id
                                ]);
                            }
                        }
                    }

                    // if ($mail_recipient->recipient_type === "mailing_list") {
                    //     //$mailing_list_known = MailingList::where('laili', $mail_recipient->recipient_id)->first();
                    //     $mailing_list_known = null;
                    //     if (is_null($corporation_known)) {
                    //         $retrieve_corporation = $this->eve->retrieve_corporation_data($mail_recipient->recipient_id);
                    //         $corporation_data = EVECorporation::create([
                    //             'corporation_id' => $retrieve_corporation['corporation_id'],
                    //             'corporation_name' => $retrieve_corporation['corporation_name']
                    //         ]);
                    //         $recipient_data[] = [
                    //             'recipient_id' => $corporation_data->character_id,
                    //             'recipient_name' => $corporation_data->character_name,
                    //             'recipient_type' => "corporation"
                    //         ];
                    //
                    //     }
                    // }
                }

                $sender_known = MailRecipient::where('recipient_id', $mail_header->from)->first();
                if (is_null($sender_known)) {
                    $is_queued = Queue::where('queue_id', $mail_header->from)->first();
                    if (is_null($is_queued)) {
                        $PlaceInQueue = Queue::create([
                            'queue_id' => $mail_header->from
                        ]);
                    }
                }

                MailHeader::create([
                    'character_id' => $token->character_id,
                    'mail_id' => $mail_header->mail_id,
                    'mail_subject' => $mail_header->subject,
                    'mail_sender' => $mail_header->from,
                    'mail_sent_date' => $mail_header->timestamp,
                    'mail_labels' => implode(',',$mail_header->labels),
                    'mail_recipient' => json_encode($mail_header->recipients),
                    'is_read' => $mail_header->is_read
                ]);
            }
            MailHeaderUpdate::updateOrCreate([
                'character_id' => $token->character_id
            ], [
                'last_header_update' => Carbon::now()->toDateTimeString()
            ]);
        }


        return true;
    }



    public function id_search($search_string)
    {
        $get_search = $this->eve->get_search($search_string);
        if ($get_search && $get_search->httpStatusCode == 200) {
            foreach ($get_search->response as $response) {
                $firstOrCreate = MailRecipient::firstOrCreate([
                    'recipient_id' => $response->id,
                    'recipient_name' => $response->name,
                    'recipient_type' => $response->category
                ]);
            }
            return count($get_search->response);
        }
        return false;
    }

    public function get_mail_body ($mail_id)
    {
        $mail_header = MailHeader::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        $token = $this->refresh_token(Token::where('character_id', $mail_header->character_id)->first());
        $mail_body = $this->eve->get_character_mail_body($token, $mail_id);
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

    public function send_message($token, $message_payload)
    {
        $job = (new PostCharacterMail($token, $message_payload))
                ->delay(Carbon::now()->addSeconds(5));
        dispatch($job);
        return true;

    }

    public function mark_mail_read($mail_id)
    {
        $token = $this->refresh_token(Token::where('character_id', Auth::user()->character_id)->first());
        $data = [
            "read" => true
        ];
        $job = (new UpdateMetaData($token, $mail_id, $data))
                ->delay(Carbon::now()->addSeconds(5));
        dispatch($job);
        return true;
    }

    public function mark_mail_unread($mail_id)
    {
        $token = $this->refresh_token(Token::where('character_id', Auth::user()->character_id)->first());
        $data = [
            "read" => false
        ];
        $job = (new UpdateMetaData($token, $mail_id, $data))
                ->delay(Carbon::now()->addSeconds(5));
        dispatch($job);
        return true;

    }

    public function delete_mail ($mail_id)
    {

        $mail_body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        $token = $this->refresh_token(Token::where('character_id', $mail_body->character_id)->first());
        $delete_mail_header = $this->eve->delete_mail_header($token, $mail_id);
        if ($delete_mail_header->httpStatusCode == 204) {
            MailHeader::where(['character_id' => $token->character_id, 'mail_id' => $mail_id])->delete();
            MailBody::where(['character_id' => $token->character_id, 'mail_id' => $mail_id])->delete();

            return true;
        }
        return false;

    }

    public function refresh_token (Token $token)
    {
        if (Carbon::now()->toDateTimeString() > $token->token_expiry) {
            return $this->eve->refresh_token($token);
        }
        return $token;
    }

}
