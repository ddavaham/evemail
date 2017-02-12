
<?php
// link to kms
$formattedMessage = preg_replace('/<a href="killReport:(\d+):(\w+)">/', '<a href="https://beta.eve-kill.net/kill/\1/#\2" target="_blank">', $formattedMessage);
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


@foreach (Session::get('recipients') as $recipient)

    @if ($recipient->recipient_type === "character")
        <a href="https://evewho.com/pilot/{{ implode('+', explode(' ', $recipient->recipient_name)) }}" class="btn btn-default" target="_blank">{{ $recipient->recipient_name }}</a>
    @endif
    @if ($recipient->recipient_type === "corporation")
        <a href="https://evewho.com/corp/{{ implode('+', explode(' ', $recipient->recipient_name)) }}" class="btn btn-default" target="_blank">{{ $recipient->recipient_name }}</a>
    @endif
    @if ($recipient->recipient_type === "alliance")
        <a href="https://evewho.com/alli/{{ implode('+', explode(' ', $recipient->recipient_name)) }}" class="btn btn-default" target="_blank">{{ $recipient->recipient_name }}</button>
    @endif
@endforeach


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
                'recipient_name.required' => "We need to know who search for. Please type a name to search for.",
                'recipient_name.min' => "Please give us more letters to search for. A search string that short will return to many results. The search parameters must be atleast :min character long",
                'recipient_name.max' => "You gave us to much to work with. We need your search string to be shorter :max",
                'recipient_name.required' => "We need to know who search for. Please type a name to search for.",
                'recipient_name.min' => "Please give us more letters to search for. A search string that short will return to many results. The search parameters must be atleast :min character long",
                'recipient_name.max' => "You gave us to much to work with. We need your search string to be shorter :max",
            ]);
            if ($validator->fails()) {
                return redirect()->route('mail.new.recipient')->withErrors($validator)->withInput();
            }
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
        if (!$request->session()->has('mail.contents')){
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
        if (!$request->session()->has('recipients') || !$request->session()->has('mail.contents')){
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
        $message_payload['subject'] = $request->session()->get('mail.contents.subject');
        $message_payload['body'] = $request->session()->get('mail.contents.body');
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
                if ($request->get('search') === "ccp" && !is_null($request->get('recipient'))) {
                    $id_search = $this->mail->id_search($request->get('recipient'));
                    if (!is_numeric($id_search) || $id_search == 0) {
                        $request->session()->flash('alert', [
                            "header" => "Search Complete.",
                            'message' => "Unfortunately, CCP did not have an additional results. Please try again with a different search phrase.",
                            'type' => 'info',
                            'close' => 1
                        ]);
                        return redirect()->route('mail.new', ['label_id' => 1]);
                    }

                } else {
                    $request->session()->flash('alert', [
                        "header" => "Houston, We have an problem",
                        'message' => "We were unable to correctly process your search. Please try again.",
                        'type' => 'info',
                        'close' => 1
                    ]);
                    return redirect()->route('mail.new', ['step_id' => 1]);
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
