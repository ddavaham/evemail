<?php

namespace EVEMail\Http\Controllers;

use DB;
use Validator;
use Session;
use Curl\Curl;
use EVEMail\Token;
use EVEMail\Queue;
use EVEMail\MailHeader;
use EVEMail\MailHeaderUpdate;
use Carbon\Carbon;
use EVEMail\MailBody;
use EVEMail\MailLabel;
use EVEMail\MailList;
use EVEMail\MailRecipient;
use EVEMail\Http\Controllers\MailController;
use EVEMail\Http\Controllers\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{

    public $request, $mail, $token;

    public function __construct(Request $request)
    {
        $this->mail = new MailController($request);
        $this->token = new TokenController($request);
        $this->http = new \EVEMail\Http\Controllers\HttpController();
        $this->request = $request;
    }

    public function index ()
    {
        return view('home');
    }

    public function about_us ()
    {
        return view('about');
    }

    public function services ()
    {
        return view('services');
    }

    public function dashboard ($label = null)
    {
        if (Auth::user()->is_new) {
            return redirect()->route('dashboard.welcome');
        }

        if ($this->request->session()->has('mail') || $this->request->session()->has('recipients')) {
            $this->request->session()->forget('mail');
            $this->request->session()->forget('recipients');
        }

        $mail_headers = MailHeader::where('mail_header.character_id', Auth::user()->character_id);
        $mail_headers->update(['is_known' => 1]);
        if (!is_null($label)) {
            $mail_headers = $mail_headers->whereRaw('FIND_IN_SET('. $label .',mail_header.mail_labels) > 0');
        }
        $mail_headers = $mail_headers->orderby('mail_header.mail_sent_date', 'desc')->leftJoin('mail_recipient', 'mail_header.mail_sender', '=', 'mail_recipient.recipient_id');
        $mail_headers = $mail_headers->get();
        $this->update_label_unread_counter(Auth::user()->character_id, null, "sync");
        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailRecipient::where(['character_id' => Auth::user()->character_id, 'recipient_type' => "mailing_list"])->get();

        return view('pages.dashboard', [
            'mail_headers' => $mail_headers,
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists,
            'label_id' => $label
        ]);
    }
    public function multiedit ($label)
    {
        if (Auth::user()->is_new) {
            return redirect()->route('dashboard.welcome');
        }

        if ($this->request->isMethod('post')) {
            if ($this->request->get('action') === "read") {
                $mail_ids = $this->request->get('multiedit');
                $mail_headers = MailHeader::where('character_id', Auth::user()->character_id)->whereIn('mail_id', $mail_ids)->get();
                foreach ($mail_headers as $header) {
                    if ($header->is_read) {
                        $this->mail->mark_mail_unread($header->character_id, $header->mail_id);
                    }
                    if (!$header->is_read) {
                        $this->mail->mark_mail_read($header->character_id, $header->mail_id);
                    }
                }
                return redirect()->route('dashboard.multiedit', ['label_id' => $label]);
            }
            if ($this->request->get('action') === "delete") {
                foreach ($this->request->get('multiedit') as $item) {
                    $this->mail->delete_mail(Auth::user()->character_id, $item);
                }
                $this->request->session()->flash('alert', [
                    'header' => "Delete Queued Successfully",
                    'message' => "Your request to delete mail has been submitted successfully. Depending on the load on the server, it my take a minute for the messages to actually disappear from this inbox and in game.",
                    'type' => "info",
                    'close' => 1
                ]);
                return redirect()->route('dashboard.multiedit', ['label_id' => $label]);
            }
        }

        $mail_headers = MailHeader::where('mail_header.character_id', Auth::user()->character_id);
        $mail_headers->update(['is_known' => 1]);
        if (!is_null($label)) {
            $mail_headers = $mail_headers->whereRaw('FIND_IN_SET('. $label .',mail_header.mail_labels) > 0');
        }
        $mail_headers = $mail_headers->orderby('mail_header.mail_sent_date', 'desc')->leftJoin('mail_recipient', 'mail_header.mail_sender', '=', 'mail_recipient.recipient_id');
        $mail_headers = $mail_headers->get();
        $this->update_label_unread_counter(Auth::user()->character_id, null, "sync");
        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailRecipient::where(['character_id' => Auth::user()->character_id, 'recipient_type' => "mailing_list"])->get();

        return view('pages.multiedit', [
            'mail_headers' => $mail_headers,
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists,
            'label_id' => $label
        ]);
    }

    public function dashboard_welcome()
    {
        (!Auth::user()->is_new) ? redirect()->route('dashboard') : null;

        if ($this->request->isMethod('post')) {
            $mail_labels = $this->mail->get_character_mail_labels(Auth::user()->character_id);
            if ($mail_labels instanceof Token) {
                if ($mail_labels->disabled) {
                    $this->request->session()->flash('alert', [
                        "header" => "Your Token has been disabled.",
                        'message' => "Our system has detected that we were not authorized to use your token on your behalf. Please login to our system again so that we are authorized.",
                        'type' => 'info',
                        'close' => 1
                    ]);
                    Auth::logout();
                    return redirect()->route('login');
                } else {
                    $this->request->session()->flash('alert', [
                        "header" => "An Error has occurred.",
                        'message' => "Our system encountered an error when attempting to fetch your data. Please try again",
                        'type' => 'info',
                        'close' => 1
                    ]);
                    return redirect()->route('dashboard.welcome');
                }
            }
            $mailing_lists = $this->mail->get_character_mailing_lists (Auth::user()->character_id);
            if ($mailing_lists instanceof Token) {
                if ($mailing_lists->disabled) {
                    $this->request->session()->flash('alert', [
                        "header" => "Your Token has been disabled.",
                        'message' => "Our system has detected that we were not authorized to use your token on your behalf. Please login to our system again so that we are authorized.",
                        'type' => 'info',
                        'close' => 1
                    ]);
                    Auth::logout();
                    return redirect()->route('login');
                } else {
                    $this->request->session()->flash('alert', [
                        "header" => "An Error has occurred.",
                        'message' => "Our system encountered an error when attempting to fetch your data. Please try again",
                        'type' => 'info',
                        'close' => 1
                    ]);
                    return redirect()->route('dashboard.welcome');
                }
            }
            $mail_headers = $this->mail->get_character_mail_headers(Auth::user()->character_id);
            if ($mail_headers instanceof Token) {
                if ($mail_headers->disabled) {
                    $this->request->session()->flash('alert', [
                        "header" => "Your Token has been disabled.",
                        'message' => "Our system has detected that we were not authorized to use your token on your behalf. Please login to our system again so that we are authorized.",
                        'type' => 'info',
                        'close' => 1
                    ]);
                    Auth::logout();
                    return redirect()->route('login');
                } else {
                    $this->request->session()->flash('alert', [
                        "header" => "An Error has occurred.",
                        'message' => "Our system encountered an error when attempting to fetch your data. Please try again",
                        'type' => 'info',
                        'close' => 1
                    ]);
                    dd($mail_headers);
                    return redirect()->route('dashboard.welcome');
                }
            }

            if ($mail_headers instanceof Curl && $mail_labels instanceof Curl && $mailing_lists instanceof Curl) {


                if ($mail_headers->httpStatusCode == 200 && $mail_labels->httpStatusCode == 200 && $mailing_lists->httpStatusCode == 200) {
                    \EVEMail\User::where('character_id', Auth::user()->character_id)->update([
                        'is_new' => 0
                    ]);
                    $this->request->session()->flash('alert', [
                        "header" => "Mail Downloaded Successfully",
                        'message' => "We have downloaded your mails successfully. Bear with us while we continue downloading the names of all the character that are part of those mails. You can access your mails, but until our minions have reached out to CCP to get the character data for those emails, we won't know whose name to display to you. Thanks for using EVEMail.Space",
                        'type' => 'success',
                        'close' => 1
                    ]);
                    return redirect()->route('dashboard');
                } else {
                    if ($mail_headers->httpStatusCode !== 200) {
                        $this->request->session()->flash('alert', [
                            "header" => "Unable to Download Your Mail Headers",
                            'message' => "CCP ESI API is responding with a less than satisfactory response at the moment. Depending on what it is, then you API Token may have been disabled and you will have to log back into this service. If you have any questions, please send a mail in game to David Davaham.",
                            'type' => 'info',
                            'close' => 1
                        ]);
                    } else if ($mail_labels->httpStatusCode !== 200) {
                        $this->request->session()->flash('alert', [
                            "header" => "Unable to Download Mail Labels",
                            'message' => "CCP ESI API is responding with a less than satisfactory response at the moment. Depending on what it is, then you API Token may have been disabled and you will have to log back into this service. If you have any questions, please send a mail in game to David Davaham.",
                            'type' => 'info',
                            'close' => 1
                        ]);
                    } else if ($mailing_lists->httpStatusCode !== 200) {
                        $this->request->session()->flash('alert', [
                            "header" => "Unable to Download Mailing Lists",
                            'message' => "CCP ESI API is responding with a less than satisfactory response at the moment. Depending on what it is, then you API Token may have been disabled and you will have to log back into this service. If you have any questions, please send a mail in game to David Davaham. If you do not have any mailing lists on this account, please let him know that as well.",
                            'type' => 'info',
                            'close' => 1
                        ]);
                    }
                    return redirect()->route('dashboard.welcome');
                }
            }

        }
        return view('pages.welcome');
    }

    public function dashboard_fetch ()
    {
        $update_headers = $this->mail->get_character_mail_headers(Auth::user()->character_id);
        $parameters = [];
        $user_preferences = Auth::user()->preferences;
        if (!is_null($user_preferences)) {

            $user_preferences = json_decode($user_preferences, true);

            if (isset($user_preferences['dashboard_default_label'])) {
                $parameters['label'] = $user_preferences['dashboard_default_label'];
            }

        }

        if ($update_headers instanceof Token) {
            if ($update_headers->disabled) {
                $this->request->session()->flash('alert', [
                    "header" => "Your Token has been disabled.",
                    'message' => "Our system has detected that we were not authorized to use your token on your behalf. Please login to our system again so that we are authorized.",
                    'type' => 'info',
                    'close' => 1
                ]);
                Auth::logout();
                return redirect()->route('login');
            } else {
                $this->request->session()->flash('alert', [
                    "header" => "An Error has occurred.",
                    'message' => "Our system encountered an error when attempting to fetch your data. Please try again",
                    'type' => 'info',
                    'close' => 1
                ]);

                return redirect()->route('dashboard', $parameters);
            }
        }
        if ($update_headers->httpStatusCode == 200) {
            $this->request->session()->flash('alert', [
                "header" => "Mailbox Updated Successfully",
                'message' => "We have successfully updated your inbox.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard', $parameters);
        } else if ($update_headers->httpStatusCode > 200) {
            if (Auth::user()->token()->first()->disabled) {
                $this->request->session()->flash('alert', [
                    'header' => "Disabled Token Detected",
                    'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect()->route('logout');
            }
            $this->request->session()->flash('alert', [
                "header" => "Inbox Out of Date",
                'message' => "Unable to update mailbox at this time. You maybe missing some mails.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('dashboard', $parameters);
        }
    }

    public function add_recipient($recipient_id = null)
    {
            $data = [];
            if ($this->request->isMethod('post')) {
                $validator = Validator::make($this->request->all(), [
                    'recipient_name' => "required|min:5",
                ],[
                    'recipient_name.required' => "We need to know who search for. Please type a name to search for.",
                    'recipient_name.min' => "Please give us more letters to search for. A search string that short will return to many results. The search parameters must be atleast :min character long",
                    'recipient_name.max' => "You gave us to much to work with. We need your search string to be shorter :max",
                ]);
                if ($validator->fails()) {
                    return redirect()->route('mail.send.recipient')->withErrors($validator)->withInput();
                }
                $data = [];
                if (!is_null($this->request->get('search'))) {
                    if ($this->request->get('search') === "ccp" && !is_null($this->request->get('recipient_name'))) {
                        $id_search = $this->mail->id_search($this->request->get('recipient_name'));
                        if ($id_search instanceof Curl) {
                            if ($id_search->httpStatusCode !== 200) {
                                $this->request->session()->flash('alert', [
                                    "header" => "Search Complete.",
                                    'message' => "Unfortunately, CCP did not have an additional results. Please try again with a different search phrase.",
                                    'type' => 'info',
                                    'close' => 1
                                ]);
                                return redirect()->route('mail.send.recipient');
                            }
                        } else {
                            $this->request->session()->flash('alert', [
                                "header" => "Unexpected Results",
                                'message' => "I saw that going differently in my head. Try again and lets see if it works this time.",
                                'type' => 'info',
                                'close' => 1
                            ]);
                            return redirect()->route('mail.send.recipient');
                        }
                    } else {
                        $this->request->session()->flash('alert', [
                            "header" => "Houston, We have an problem",
                            'message' => "We were unable to correctly process your search. Please try again.",
                            'type' => 'info',
                            'close' => 1
                        ]);
                        return redirect()->route('mail.send.recipient');
                    }
                }
                $data['results'] = MailRecipient::where('recipient_name','like','%'.$this->request->get('recipient_name').'%')->where('recipient_type','character')->get();
            } else if (!is_null($recipient_id)) {
                $sessions = $this->request->session();
                $recipients = ($sessions->has('recipients')) ? $sessions->get('recipients'): [];
                $recipients[$recipient_id] = MailRecipient::where(['recipient_id' => $recipient_id, 'recipient_type' => "character"])->first();
                $this->request->session()->put('recipients', $recipients);
                return redirect()->route('mail.send.recipient');
            }
            return view('mail.add_recipients', $data);

    }

    public function mail_reset()
    {
        $this->request->session()->forget('recipients');
        $this->request->session()->forget('mail');
        if ($this->request->to) {
          return redirect($this->request->to);
        }
        if (Session::has('is_reply')) {
            $this->request->session()->forget('is_reply');
            return redirect()->route('dashboard');
        }
        return redirect()->route('mail.send.build');
    }

    public function mail_reply_build ($mail_id, $recipient = null)
    {

      if ($this->request->isMethod('post')) {
          $validator = Validator::make($this->request->all(), [
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
              return redirect()->route('mail.reply.build', ['mail_id' => $mail_id])->withErrors($validator)->withInput();
          }
          $this->request->session()->put('mail', [
              'body' => $this->request->get('body'),
              'subject' => $this->request->get('subject')
          ]);
          return redirect()->route('mail.reply.preview', ['mail_id' => $mail_id]);
      }
      $sessions = $this->request->session();
      $recipients = ($sessions->has('recipients')) ? $sessions->get('recipients'): [];
      if ($this->request->get('remove')) {
          unset($recipients[$this->request->get('remove')]);
          array_keys($recipients);
          $this->request->session()->put('recipients', $recipients);

          return redirect()->route('mail.reply.build', ['mail_id' => $mail_id]);
      }
      $mail_header = MailHeader::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
      $mail_body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();


      if ($this->request->first_time) {
          $mail_from_to_recipient = MailRecipient::where(['recipient_id' => $mail_header->mail_sender])->first();
          if (!is_null($mail_from_to_recipient)) {
            $recipients[$mail_from_to_recipient->recipient_id] = $mail_from_to_recipient;
          }
          if (!is_null($recipient)) {
              $recipients[$recipient] = MailRecipient::where(['recipient_id' => $mail_header->mail_sender])->first();
          } else {
              foreach (json_decode($mail_header->mail_recipient, true) as $mail_recipient) {
                $recipient_data = MailRecipient::where(['recipient_id' => $mail_recipient['recipient_id']])->first();
                $recipients[$recipient_data->recipient_id] = $recipient_data;
              }
          }
          unset($recipients[Auth::user()->character_id]);
          $this->request->session()->put('recipients', $recipients);
          return redirect()->route('mail.reply.build', ['mail_id' => $mail_id]);
      }
      $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
      $mailing_lists = MailRecipient::where(['character_id' => Auth::user()->character_id, 'recipient_type' => "mailing_list"])->get();

      return view('mail.mail_reply_build', [
        'header' => $mail_header,
        'to' => $this->request->path(),
        'mail_labels' => $mail_labels,
        'mailing_lists' => $mailing_lists
      ]);
    }

    public function mail_reply_preview($mail_id)
    {

        if (!$this->request->session()->has('mail')){
            $this->request->session()->flash('alert', [
                "header" => "Invalid Page Request",
                'message' => "You must have the subject and body of your message set before you can view that page. Please use this page to set those variables.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('mail.send.build');
        }
        $mail_header = MailHeader::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        $mail_body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        $mail_from_to_recipient = MailRecipient::where(['recipient_id' => $mail_header->mail_sender])->first();
        if (!is_null($mail_from_to_recipient)) {
          $recipients[$mail_from_to_recipient->recipient_id] = $mail_from_to_recipient;
        }
        foreach (json_decode($mail_header->mail_recipient, true) as $mail_recipient) {
          $recipient_data = MailRecipient::where(['recipient_id' => $mail_recipient['recipient_id']])->first();
          $recipients[$recipient_data->recipient_id] = $recipient_data;
        }

        //Format Appended Text
        $body = "--------------------------------\r\n";
        $body .= "Subject: ". $mail_header->mail_subject."\r\n";
        $body .= "From: ".$recipients[$mail_header->mail_sender]->recipient_name."\r\n";
        $body .= "Sent: ".Carbon::createFromTimestamp(strtotime($mail_header->mail_sent_date))->format('Y.m.d g:i:s')."\r\n";
        unset($recipients[$mail_header->mail_sender]);
        $body .= "To: ";
        $x = 1;
        foreach ($recipients as $recipient) {
          $body .= $recipient->recipient_name;
          if ($x < count($recipients)) {
            $body .= ", ";
            $x++;
          }
        }
        $body .= "\r\n\r\n";
        $body .= $mail_body->mail_body;

        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailRecipient::where(['character_id' => Auth::user()->character_id, 'recipient_type' => "mailing_list"])->get();
        return view('mail.mail_reply_preview', [
            'header' => $mail_header,
            'reply_body' => $body,
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists
        ]);
    }
    public function mail_reply_send($mail_id)
    {

        if (!$this->request->session()->has('recipients') || !$this->request->session()->has('mail')){
            $this->request->session()->flash('alert', [
                "header" => "Invalid Page Request",
                'message' => "You must have recipients and a message built in order to access this page. Please use this page to rebuild your message",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('mail.reply.build');
        }
        $message_payload = [];
        $messsage_recipients = $this->request->session()->get('recipients');

        foreach ($this->request->session()->get('recipients') as $recipient) {
            $message_payload['recipients'][] = [
                'recipient_id' => $recipient->recipient_id,
                'recipient_type' =>$recipient->recipient_type
            ];
        }
        $message_payload['subject'] = $this->request->session()->get('mail.subject');


        $mail_header = MailHeader::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        $mail_body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        $mail_from_to_recipient = MailRecipient::where(['recipient_id' => $mail_header->mail_sender])->first();
        if (!is_null($mail_from_to_recipient)) {
          $recipients[$mail_from_to_recipient->recipient_id] = $mail_from_to_recipient;
        }
        foreach (json_decode($mail_header->mail_recipient, true) as $mail_recipient) {
          $recipient_data = MailRecipient::where(['recipient_id' => $mail_recipient['recipient_id']])->first();
          $recipients[$recipient_data->recipient_id] = $recipient_data;
        }

        //Format Appended Text
        $body = "<br /><br />---- This EVEMail was sent using EVEMail.Space. A Web-based Mail Client for EVEOnline. ----<br /><br />";
        $body .= "--------------------------------\r\n";
        $body .= "Subject: ". $mail_header->mail_subject."\r\n";
        $body .= "From: ".$recipients[$mail_header->mail_sender]->recipient_name."\r\n";
        $body .= "Sent: ".Carbon::createFromTimestamp(strtotime($mail_header->mail_sent_date))->format('Y.m.d g:i:s')."\r\n";
        unset($recipients[$mail_header->mail_sender]);
        $body .= "To: ";
        $x = 1;
        foreach ($recipients as $recipient) {
          $body .= $recipient->recipient_name;
          if ($x < count($recipients)) {
            $body .= ", ";
            $x++;
          }
        }
        $body .= "\r\n\r\n";
        $body .= $mail_body->mail_body;



        $message_payload['body'] = $this->request->session()->get('mail.body')."\r\n\r\n".$body;
        $message_payload['approved_cost'] = 10000;
        $send_message = $this->mail->send_message(Auth::user()->character_id, $message_payload);
        if ($send_message) {
            $this->request->session()->forget('recipients');
            $this->request->session()->forget('mail');
            $this->request->session()->flash('alert', [
                "header" => "Your Mail has been queued",
                'message' => "Your mail has been queued successfully. Our minons will send it ASAP. I promise, they are workin hard.",
                'type' => 'success',
                'close' => 1
            ]);
            return redirect()->route('dashboard');

        }

        $this->request->session()->flash('alert', [
            "header" => "Houston, there maybe a problem.",
            'message' => "We attempted to queue your message, but there was a problem. Do us a favor and click that green button ooooooone more time.",
            'type' => 'info',
            'close' => 1
        ]);
        return redirect()->route('mail.send.preview');
    }

    public function mail_send_build ()
    {
        if ($this->request->isMethod('post')) {
            $validator = Validator::make($this->request->all(), [
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
                return redirect()->route('mail.send.build')->withErrors($validator)->withInput();
            }
            $this->request->session()->put('mail', [
                'body' => $this->request->get('body'),
                'subject' => $this->request->get('subject')
            ]);
            return redirect()->route('mail.send.preview');
        }
        if ($this->request->get('remove')) {
            $recipients = $this->request->session()->get('recipients');
            unset($recipients[$this->request->get('remove')]);
            array_keys($recipients);
            $this->request->session()->put('recipients', $recipients);

            return redirect()->route('mail.send.build');
        }
        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailRecipient::where(['character_id' => Auth::user()->character_id, 'recipient_type' => "mailing_list"])->get();
        return view('mail.mail_send_build', [
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists
        ]);
    }

    public function mail_send_preview()
    {
        if (!$this->request->session()->has('mail') || !$this->request->session()->has('recipients')){
            $this->request->session()->flash('alert', [
                "header" => "Invalid Page Request",
                'message' => "You must have the recipients,subject, and body of your message set before you can view that page. Please use this page to set those variables.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('mail.send.build');
        }
        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailRecipient::where(['character_id' => Auth::user()->character_id, 'recipient_type' => "mailing_list"])->get();
        return view('mail.mail_send_preview', [
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists
        ]);
    }

    public function mail_send_send()
    {
        if (!$this->request->session()->has('recipients') || !$this->request->session()->has('mail')){
            $this->request->session()->flash('alert', [
                "header" => "Invalid Page Request",
                'message' => "You must have recipients and a message built in order to access this page. Please use this page to rebuild your message",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('mail.send.build');
        }
        $message_payload = [];
        $messsage_recipients = $this->request->session()->get('recipients');

        foreach ($this->request->session()->get('recipients') as $recipient) {
            $message_payload['recipients'][] = [
                'recipient_id' => $recipient->recipient_id,
                'recipient_type' =>$recipient->recipient_type
            ];
        }
        $message_payload['subject'] = $this->request->session()->get('mail.subject');
        $message_payload['body'] = $this->request->session()->get('mail.body'). "<br /><br />---- This EVEMail was sent using EVEMail.Space. A Web-based Mail Client for EVEOnline. ----<br />";
        $message_payload['approved_cost'] = 10000;
        $send_message = $this->mail->send_message(Auth::user()->character_id, $message_payload);
        if ($send_message instanceof Token) {
            if ($send_message->disabled) {
                $this->request->session()->flash('alert', [
                    "header" => "Your Token has been disabled.",
                    'message' => "Our system has detected that we were not authorized to use your token on your behalf. Please login to our system again so that we are authorized.",
                    'type' => 'info',
                    'close' => 1
                ]);
                Auth::logout();
                return redirect()->route('login');
            }
            return redirect()->route('dashboard');
        } else if ($send_message) {

            $this->request->session()->forget('recipients');
            $this->request->session()->forget('mail');
            $this->request->session()->flash('alert', [
                "header" => "Your Mail has been queued",
                'message' => "Your mail has been queued successfully. Our minons will send it ASAP. I promise, they are workin hard.",
                'type' => 'success',
                'close' => 1
            ]);
            return redirect()->route('dashboard');

        }
        if (Auth::user()->token()->first()->disabled) {
            $this->request->session()->flash('alert', [
                'header' => "Disabled Token Detected",
                'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('logout');
        }
        $this->request->session()->flash('alert', [
            "header" => "Houston, there maybe a problem.",
            'message' => "We attempted to queue your message, but there was a problem. Do us a favor and click that green button ooooooone more time.",
            'type' => 'info',
            'close' => 1
        ]);
        return redirect()->route('mail.send.preview');
    }

    public function mail_view($mail_id)
    {
        $header = MailHeader::where(['mail_header.character_id' => Auth::user()->character_id, 'mail_header.mail_id' => $mail_id])
        ->leftJoin('mail_recipient', 'mail_header.mail_sender', '=', 'mail_recipient.recipient_id')
        ->first();
        if (is_null($header)) {
            $this->request->session()->flash('alert', [
                "header" => "Houston, We have an problem",
                'message' => "The mail you are requesting does not exist in our database. To be safe, we have deleted it from our database. If it exists, we will detect it the next time you log in.",
                'type' => 'info',
                'close' => 1
            ]);
            MailHeader::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->delete();
            return redirect()->route('dashboard');
        }

        $mail_recipients = [];

        foreach (json_decode($header->mail_recipient, true) as $recipient) {
            $where = [
                'recipient_id' => $recipient['recipient_id']
            ];

            if ($recipient['recipient_type'] === "mailing_id") {
                $where['character_id'] = Auth::user()->character_id;
            }
            $recipient = MailRecipient::where($where)->first();
            if (is_null($recipient) && $recipient_type === "mailing_list") {
                $this->request->session()->flash('alert', [
                    "header" => "Unknown Mailing List",
                    'message' => "We don't know the mailing list that this message was sent to. Please go to the settings menu and refresh your mailing lists.",
                    'type' => 'warning',
                    'close' => 1
                ]);
            } else {
                $mail_recipients[$recipient->recipient_id] = $recipient;
            }

        }


        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailRecipient::where(['character_id' => Auth::user()->character_id, 'recipient_type' => "mailing_list"])->get();

        $body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();


        if (is_null($body)) {
            $retrieve_body = $this->mail->get_mail_body(Auth::user()->character_id, $mail_id);
            if (!$retrieve_body) {
                if (Auth::user()->token()->first()->disabled) {
                    $this->request->session()->flash('alert', [
                        'header' => "Disabled Token Detected",
                        'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                        'type' => 'danger',
                        'close' => 1
                    ]);
                    return redirect()->route('logout');
                }
                $this->request->session()->flash('alert', [
                    "header" => "Houston, We have an problem",
                    'message' => "The mail you are requesting does not exist in our database nor are we able to retreive it from CCP. That mail has probably been deleted in game or by another mail client. To be safe, we have deleted it from our database. If it exists, we will detect it the next time you log in.",
                    'type' => 'info',
                    'close' => 1
                ]);
                MailHeader::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->delete();
                return redirect()->route('dashboard');
            }
            $body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        }
        if (!$header->is_read) {
            $update_label_unread_counter = $this->update_label_unread_counter(Auth::user()->character_id, $mail_id, "sub");

            $mark_mail_read = $this->mail->mark_mail_read(Auth::user()->character_id, $header->mail_id);
            if (!$mark_mail_read) {
                $this->request->session()->flash('alert', [
                    'header' => "Disabled Token Detected",
                    'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect()->route('logout');
            }
        }
        $mail_labels = MailLabel::where('character_id', Auth::user()->character_id)->orderby('label_id', 'asc')->get();
        $mailing_lists = MailRecipient::where(['character_id' => Auth::user()->character_id, 'recipient_type' => "mailing_list"])->get();
        return view('mail.mail_view', [
            'header' => $header,
            'body' => $body,
            'mail_labels' => $mail_labels,
            'mailing_lists' => $mailing_lists,
            'mail_recipients' => $mail_recipients
        ]);

    }

    public function unread_mail ($mail_id)
    {
        $body = MailBody::where('mail_id', $mail_id)->first();
        if (is_null($body)){
            $this->request->session()->flash('alert', [
                "header" => "Houston, We have an problem",
                'message' => "The mail you are requesting does not exist in our database. Please hold tight and see if our minions can find it.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $update_label_unread_counter = $this->update_label_unread_counter(Auth::user()->character_id, $mail_id, "add");
        $mark_mail_unread = $this->mail->mark_mail_unread(Auth::user()->character_id, $mail_id);
        if (!$mark_mail_unread && Auth::user()->token()->first()->disabled) {
            $this->request->session()->flash('alert', [
                'header' => "Disabled Token Detected",
                'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('logout');
        }
        $this->request->session()->flash('alert', [
                'message' => "As far as we are concerned, you never read that mail.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard');

        return redirect()->route('dashboard');
    }
    public function delete_mail ($mail_id)
    {
        $body = MailBody::where(['character_id' => Auth::user()->character_id, 'mail_id' => $mail_id])->first();
        if (is_null($body)){
            $this->request->session()->flash('alert', [
                "header" => "Houston, We have an problem",
                'message' => "The mail that you are looking for does not exists or does not belong to you. Please try again. If you continue to get this error, please use the contact for to send us a message.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $delete_mail = $this->mail->delete_mail(Auth::user()->character_id, $mail_id);
        if (!$delete_mail && Auth::user()->token()->first()->disabled) {
            $this->request->session()->flash('alert', [
                'header' => "Disabled Token Detected",
                'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('logout');
        } else if (!$delete_mail) {
            $this->request->session()->flash('alert', [
                'message' => "We apologize for the inconvienence, put we are unable to update the status of the mail at this time. Please try again in 1 minute.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $this->request->session()->flash('alert', [
            "header" => "That message was deleted successfully",
            'message' => "You have successfully delete the message with id {$body->mail_id}.",
            'type' => 'success',
            'close' => 1
        ]);
        return redirect()->route('dashboard');
    }

    public function update_label_unread_counter($character_id, $mail_id = null, $action)
    {
        if ($action === "sync") {
            $label_array = [];
            $headers = MailHeader::where(['character_id' => $character_id, 'is_read' => 0])->get();
            foreach ($headers as $header) {
                $labels = explode(',', $header->mail_labels);
                foreach ($labels as $label) {
                    (isset($label_array[$label])) ? $label_array[$label] += 1 : $label_array[$label] = 1;
                }
            }
            foreach ($label_array as $label_id=>$label_count){
                MailLabel::where(['character_id' => $character_id, 'label_id' => $label_id])->update([
                    'label_unread_count' => $label_count
                ]);
            }
            return true;
        }


        $header = MailHeader::where(['character_id' => $character_id, 'mail_id' => $mail_id])->first();
        $labels = explode(',', $header->mail_labels);
        foreach ($labels as $label) {
            $mail_label = MailLabel::where(['character_id' => $character_id, 'label_id' => $label])->first();
            if (!is_null($mail_label)) {
                $unread_count = $mail_label->label_unread_count;
                if ($action === "add") {
                    $unread_count = $unread_count+1;
                }
                if ($action === "sub") {
                    if ($unread_count <= 0) {
                        $this->request->session()->flash('alert', [
                            "header" => "Houston, We have an problem",
                            'message' => "Unread count for current label is zero, but you have mail that is unread for this label. Please click Update Mail Labels below so that we can resync your labels unread count to provide you an accurate count.",
                            'type' => 'danger',
                            'close' => 1
                        ]);
                        return redirect()->route('settings');
                    }
                    $unread_count = $unread_count-1;
                }

                MailLabel::where(['character_id' => $character_id, 'label_id' => $label])->update([
                    'label_unread_count' => $unread_count
                ]);
            }
        }
    }


    public function update_mail_labels()
    {
        $update = $this->mail->get_character_mail_labels(Auth::user()->character_id);
        if (!$update) {
            if (Auth::user()->token()->first()->disabled) {
                $this->request->session()->flash('alert', [
                    'header' => "Disabled Token Detected",
                    'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect()->route('logout');
            }
            $this->request->session()->flash('alert', [
                'header' => "An Error has Occured",
                'message' => "We were unable to update your label at this time. Please try again in a few minutes.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $this->request->session()->flash('alert', [
            'message' => "Your labels have been updated successfully",
            'type' => 'success',
            'close' => 1
        ]);
        return redirect()->route('dashboard');
    }
    public function update_mailing_lists()
    {
        $update = $this->mail->get_character_mailing_lists(Auth::user()->character_id);
        if (!$update) {
            if (Auth::user()->token()->first()->disabled) {
                $this->request->session()->flash('alert', [
                    'header' => "Disabled Token Detected",
                    'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect()->route('logout');
            }
            $this->request->session()->flash('alert', [
                'header' => "An Error has Occured",
                'message' => "We were unable to update your mailing lists at this time. Please try again in a few minutes.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('dashboard');
        }
        $this->request->session()->flash('alert', [
            'message' => "We've submitted your request to update your mailing lists.",
            'type' => 'info',
            'close' => 1
        ]);
        return redirect()->route('dashboard');
    }

    public function get_token ($character_id)
    {
        $token = $this->token->get_token($character_id);
        if ($token->disabled) {
            $this->request->session()->flash('alert', [
                'header' => "Disabled Token Detected",
                'message' => "Your Access Token has been disabled by our system due to an invalid response code from CCP. Please login again to correct this issue.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('logout');
        }
        return $token;
    }

    public function testing()
    {

    }


}
