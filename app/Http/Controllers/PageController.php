<?php

namespace EVEMail\Http\Controllers;


use Validator;
use EVEMail\Token;
use EVEMail\MailHeader;
use Carbon\Carbon;
use EVEMail\MailBody;
use EVEMail\MailLabel;
use EVEMail\MailList;
use EVEMail\MailRecipient;
use EVEMail\Http\Controllers\MailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function __construct()
    {
        $this->mail = new MailController();
    }

    public function index ()
    {
        return view('home');
    }

    public function dashboard (Request $request, $label = null)
    {

        if (Auth::user()->is_new) {
            return redirect()->route('dashboard.welcome');
        }

        $mail_headers = MailHeader::where('mail_header.character_id', Auth::user()->character_id);
        if (!is_null($label)) {
            $mail_headers = $mail_headers->whereRaw('FIND_IN_SET('. $label .',mail_header.mail_labels) > 0');
        }
        $mail_headers = $mail_headers->orderby('mail_header.mail_sent_date', 'desc')->leftJoin('mail_recipient', 'mail_header.mail_sender', '=', 'mail_recipient.recipient_id');
        $mail_headers = $mail_headers->get();
        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailList::where('character_id', Auth::user()->character_id)->get();

        return view('pages.dashboard', [
            'mail_headers' => $mail_headers,
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists
        ]);
    }

    public function settings()
    {
        return view ('pages.settings');
    }


    public function dashboard_welcome()
    {
        return view('pages.welcome');
    }

    public function dashboard_fetch (Request $request)
    {
        $token = Token::where('character_id', Auth::user()->character_id)->first();
        $update_headers = $this->mail->get_character_mail_headers($token);
        if ($update_headers) {
            $request->session()->flash('alert', [
                "header" => "Mailbox Updated Successfully",
                'message' => "We have successfully updated your inbox.",
                'type' => 'info',
                'close' => 1
            ]);
            $this->mail->process_queue();
            return redirect()->route('dashboard');
        } else {
            $request->session()->flash('alert', [
                "header" => "Inbox Out of Date",
                'message' => "Unable to update mailbox at this time. You maybe missing some mails.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
    }

    public function new_mail_process(Request $request, $step, $recipient_id = null)
    {
        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailList::where('character_id', Auth::user()->character_id)->get();

        if ($step == 1) {
            if ($request->isMethod('post')) {
                $validator = Validator::make($request->all(), [
                    'body' => "required|min:15",
                    'subject' => "required|min:5|max:100",
                ],[
                    'body.required' => "You do know the point of an email is to actually communicate right? Type something in the body down there for us to send your recipient",
                    'body.min' => "Alright now, not quite enough beef to the body of this thing. Please make the body off your message atleast :min",
                    'body.max' => "Alright Mr./Miss Novelist. Im sure this is a well thoughout email, but please limit it to :max characters.",
                    'subject.required' => "Try typing a subject down there. It helps let the reader know what the email is regarding.",
                    'subject.min' => "Yikes!! That is a short subject line there. Be a little nicer to your recipients and add some letters. Make it atleast :min characters long",
                    'subject.max' => " You do realize that at this point, you subject line mine as well be in the body of you email. That is a little log for us. Shoten it to belong :max characters",
                ]);
                if ($validator->fails()) {
                    return redirect()->route('mail.new.recipient')->withErrors($validator)->withInput();
                }
                $request->session()->put('mail', [
                    'body' => $request->get('body'),
                    'subject' => $request->get('subject')
                ]);
                return redirect()->route('mail.new', ['step_id' => 2]);
            }
            if ($request->get('remove')) {
                $recipients = $request->session()->get('recipients');
                unset($recipients[$request->get('remove')]);
                array_keys($recipients);
                $request->session()->put('recipients', $recipients);

                return redirect()->route('mail.new', ['label_id' => 1]);
            }
            return view('mail.build_message', [
                'mail_labels' => $mail_labels,
                'mailing_lists' => $mailing_lists
            ]);
        }
        if ($step == 2) {
            if (!$request->session()->has('mail')){
                $request->session()->flash('alert', [
                    "header" => "Invalid Page Request",
                    'message' => "You must have the subject and body of your message set before you can view that page. Please use this page to set those variables.",
                    'type' => 'info',
                    'close' => 1
                ]);
                return redirect()->route('mail.new', ['step_id' => 1]);
            }

            return view('mail.preview_message', [
                'mail_labels' => $mail_labels,
                'mailing_lists' => $mailing_lists
            ]);
        }
        if ($step == 3) {
            if (!$request->session()->has('recipients') || !$request->session()->has('mail')){
                $request->session()->flash('alert', [
                    "header" => "Invalid Page Request",
                    'message' => "You must have recipients and a message built in order to access this page. Please use this page to rebuild your message",
                    'type' => 'info',
                    'close' => 1
                ]);
                return redirect()->route('mail.new', ['step_id' => 1]);
            }
            $message_payload = [];
            $messsage_recipients = $request->session()->get('recipients');

            foreach ($request->session()->get('recipients') as $recipient) {
                $message_payload['recipients'][] = [
                    'recipient_id' => $recipient->recipient_id,
                    'recipient_type' =>$recipient->recipient_type
                ];
            }
            $message_payload['subject'] = $request->session()->get('mail.subject');
            $message_payload['body'] = $request->session()->get('mail.body');
            $message_payload['approved_cost'] = 10000;
            $token = Token::where('character_id', Auth::user()->character_id)->first();
            $send_message = $this->mail->send_message($token, $message_payload);
            if ($send_message) {
                $request->session()->forget('recipients');
                $request->session()->forget('mail');
                $request->session()->flash('alert', [
                    "header" => "Your Mail has been queued",
                    'message' => "Your mail has been queued successfully. Our minons will send it ASAP. I promise, they are workin hard.",
                    'type' => 'success',
                    'close' => 1
                ]);
                return redirect()->route('dashboard');

            }
            $request->session()->flash('alert', [
                "header" => "Houston, there maybe a problem.",
                'message' => "We attempted to queue your message, but there was a problem. Do us a favor and click that green button ooooooone more time.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('mail.new', ['step_id' => 3]);
        }
    }



    public function add_recipient(Request $request, $recipient_id = null)
    {
            $data = [];
            if ($request->isMethod('post')) {
                $validator = Validator::make($request->all(), [
                    'recipient_name' => "required|min:5",
                ],[
                    'recipient_name.required' => "We need to know who search for. Please type a name to search for.",
                    'recipient_name.min' => "Please give us more letters to search for. A search string that short will return to many results. The search parameters must be atleast :min character long",
                    'recipient_name.max' => "You gave us to much to work with. We need your search string to be shorter :max",
                ]);
                if ($validator->fails()) {
                    return redirect()->route('mail.new.recipient')->withErrors($validator)->withInput();
                }
                $data = [];
                if (!is_null($request->get('search'))) {
                    if ($request->get('search') === "ccp" && !is_null($request->get('recipient_name'))) {
                        $id_search = $this->mail->id_search($request->get('recipient_name'));
                        if (!is_numeric($id_search) || $id_search == 0) {
                            $request->session()->flash('alert', [
                                "header" => "Search Complete.",
                                'message' => "Unfortunately, CCP did not have an additional results. Please try again with a different search phrase.",
                                'type' => 'info',
                                'close' => 1
                            ]);
                            return redirect()->route('mail.new.recipient');
                        }

                    } else {
                        $request->session()->flash('alert', [
                            "header" => "Houston, We have an problem",
                            'message' => "We were unable to correctly process your search. Please try again.",
                            'type' => 'info',
                            'close' => 1
                        ]);
                        return redirect()->route('mail.new.recipient');
                    }

                }
                $data['results'] = MailRecipient::where('recipient_name','like','%'.$request->get('recipient_name').'%')->where('recipient_type','character')->get();
            } else if (!is_null($recipient_id)) {
                $sessions = $request->session();
                $recipients = ($sessions->has('recipients')) ? $sessions->get('recipients'): [];
                $recipients[] = MailRecipient::where(['recipient_id' => $recipient_id, 'recipient_type' => "character"])->first();
                $recipients = array_combine(range(1, count($recipients)), array_values($recipients));
                $request->session()->put('recipients', $recipients);
                return redirect()->route('mail.new.recipient');
            }
            return view('mail.add_recipients', $data);

    }

    public function mail_reset(Request $request)
    {
        $request->session()->forget('recipients');
        $request->session()->forget('mail.contents');
        return redirect()->route('mail.new', ['step_id' => 1]);
    }

    public function read_mail(Request $request, $mail_id)
    {
        $header = MailHeader::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])
        ->leftJoin('mail_recipient', 'mail_header.mail_sender', '=', 'mail_recipient.recipient_id')
        ->first();
        if (is_null($header)) {
            $request->session()->flash('alert', [
                "header" => "Houston, We have an problem",
                'message' => "The mail you are requesting does not exist in our database. Please hold tight and see if our minions can find it.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }

        $mail_recipients = [];
        foreach (json_decode($header->mail_recipient, true) as $recipient) {
            if ($recipient['recipient_type'] === "mailing_list") {
                $get_character_mail_list = MailList::where(['character_id' => Auth::user()->character_id, 'mailing_list_id' => $recipient['recipient_id']])->first();
                if (is_null($get_character_mail_list)) {
                    $request->session()->flash('alert', [
                        "header" => "Unknown Mailing List",
                        'message' => "We don't know the mailing list that this message was sent to. Please go to the settings menu and refresh your mailing lists.",
                        'type' => 'warning',
                        'close' => 1
                    ]);
                }
                $mail_recipients[] = [
                    'recipient_id' => $recipient['recipient_id'],
                    'recipient_name' => (!is_null($get_character_mail_list)) ? $get_character_mail_list->mailing_list_name : "Unable to Parse Mailing List ID"
                ];
            } else {
                $recipient = MailRecipient::where('recipient_id', $recipient['recipient_id'])->first();

                $mail_recipients[] = [
                    'recipient_id' => $recipient->recipient_id,
                    'recipient_name' => $recipient->recipient_name
                ];
            }
        }


        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailList::where('character_id', Auth::user()->character_id)->get();

        $body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();


        if (is_null($body)) {
            $retrieve_body = $this->mail->get_mail_body($mail_id);

            if (!isset($retrieve_body->error)) {

                $body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
            } else {

                $request->session()->flash('alert', [
                    "header" => "Houston, We have an problem",
                    'message' => "The mail you are requesting does not exist in our database nor are we able to retreive it from CCP. Please check to see if the mail exists via the EVE Online Client.",
                    'type' => 'info',
                    'close' => 1
                ]);
                return redirect()->route('dashboard');
            }
        }
        if (!$header->is_read) {
            $mail_mail = $this->mail->mark_mail_read($header->mail_id);
        }

        return view('pages.mail', [
            'header' => $header,
            'body' => $body,
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists,
            'mail_recipients' =>$mail_recipients
        ]);

    }

    public function reply_mail(Request $request, $mail_id, $step_id)
    {
        $header = MailHeader::where('mail_id', $mail_id)
        ->leftJoin('mail_recipient', 'mail_header.mail_sender', '=', 'mail_recipient.recipient_id')
        ->first();
        if (is_null($header)) {
            $request->session()->flash('alert', [
                "header" => "Houston, We have an problem",
                'message' => "The mail you are requesting does not exist in our database. Please hold tight and see if our minions can find it.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }

        $mail_recipients = [];
        foreach (json_decode($header->mail_recipient, true) as $recipient) {
            if ($recipient['recipient_type'] === "mailing_list") {
                $get_character_mail_list = MailList::where(['character_id' => Auth::user()->character_id, 'mailing_list_id' => $recipient['recipient_id']])->first();
                if (is_null($get_character_mail_list)) {
                    $request->session()->flash('alert', [
                        "header" => "Unknown Mailing List",
                        'message' => "We don't know the mailing list that this message was sent to. Please go to the settings menu and refresh your mailing lists.",
                        'type' => 'warning',
                        'close' => 1
                    ]);
                }
                $mail_recipients[] = [
                    'recipient_id' => $recipient['recipient_id'],
                    'recipient_name' => (!is_null($get_character_mail_list)) ? $get_character_mail_list->mailing_list_name : "Unable to Parse Mailing List ID"
                ];
            } else {
                $recipient = MailRecipient::where('recipient_id', $recipient['recipient_id'])->first();

                $mail_recipients[] = [
                    'recipient_id' => $recipient->recipient_id,
                    'recipient_name' => $recipient->recipient_name
                ];
            }
        }


        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailList::where('character_id', Auth::user()->character_id)->get();

        $body = MailBody::where('mail_id', $mail_id)->first();


        if (is_null($body)) {
            $retrieve_body = $this->mail->get_mail_body($mail_id);
            if ($retreive_body) {
                $body = MailBody::where('mail_id', $mail_id)->first();
            }
        }
        if (!$header->is_read) {
            $header->update([
                'is_read' => 1
            ]);
            $this->mail->mark_mail_read($header->mail_id);
        }

        return view('pages.mail', [
            'header' => $header,
            'body' => $body,
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists,
            'mail_recipients' =>$mail_recipients
        ]);

    }

    public function unread_mail (Request $request, $mail_id)
    {
        $body = MailBody::where('mail_id', $mail_id)->first();
        if (is_null($body)){
            $request->session()->flash('alert', [
                "header" => "Houston, We have an problem",
                'message' => "The mail you are requesting does not exist in our database. Please hold tight and see if our minions can find it.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $unread_mail = $this->mail->mark_mail_unread($mail_id);
        if ($unread_mail) {
            $request->session()->flash('alert', [
                'message' => "As far as we are concerned, you never read that mail.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $request->session()->flash('alert', [
            'message' => "We apologize for the inconvienence, put we are unable to update the status of the mail at this time. Please try again in 1 minute.",
            'type' => 'danger',
            'close' => 1
        ]);
        return redirect()->route('dashboard');
    }
    public function delete_mail (Request $request, $mail_id)
    {
        $body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        if (is_null($body)){
            $request->session()->flash('alert', [
                "header" => "Houston, We have an problem",
                'message' => "The mail that you are looking for does not exists or does not belong to you. Please try again. If you continue to get this error, please use the contact for to send us a message.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $delete_mail = $this->mail->delete_mail($mail_id);
        if ($delete_mail) {
            $request->session()->flash('alert', [
                "header" => "That message was deleted successfully",
                'message' => "You have successfully delete the message with id {$body->mail_id}.",
                'type' => 'success',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $request->session()->flash('alert', [
            'message' => "We apologize for the inconvienence, put we are unable to update the status of the mail at this time. Please try again in 1 minute.",
            'type' => 'danger',
            'close' => 1
        ]);
        return redirect()->route('dashboard');
    }

    public function update_mail_labels(Request $request)
    {
        $token = Token::where('character_id', Auth::user()->character_id )->first();
        $this->mail->get_character_mail_labels($token);
        $request->session()->flash('alert', [
            'message' => "We've submitted your request to update your mailing lablels.",
            'type' => 'info',
            'close' => 1
        ]);
        return redirect()->route('settings');
    }
    public function update_mailing_lists(Request $request)
    {
        $token = Token::where('character_id', Auth::user()->character_id )->first();
        $this->mail->get_character_mailing_lists($token);
        $request->session()->flash('alert', [
            'message' => "We've submitted your request to update your mailing lists.",
            'type' => 'info',
            'close' => 1
        ]);
        return redirect()->route('settings');
    }

}
