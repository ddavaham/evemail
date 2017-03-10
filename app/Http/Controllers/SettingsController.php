<?php

namespace EVEMail\Http\Controllers;

use DB;
use Mail;
use Session;
use Validator;
use EVEMail\Token;
use Carbon\Carbon;
use EVEMail\MailBody;
use EVEMail\MailList;
use EVEMail\MailLabel;
use EVEMail\MailHeader;
use EVEMail\MailRecipient;
use Illuminate\Http\Request;
use EVEMail\Mail\EmailVerification;
use Illuminate\Support\Facades\Auth;
use EVEMail\Http\Controllers\MailController;
use EVEMail\Http\Controllers\TokenController;


class SettingsController extends Controller
{

    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function overview()
    {
        return view ('settings.overview');
    }

    public function email ()
    {
        if ($this->request->isMethod('post') && $this->request->has('action')) {
            if ($this->request->action === "delete_character_email") {
                Auth::user()->email()->delete();
                Auth::user()->update([
                    'preferences' => null
                ]);

                $this->request->session()->flash('alert', [
                    "header" => "Email Setting Updated Successfully",
                    'message' => "We have deleted your email address from our system and reset your preferences.",
                    'type' => 'info',
                    'close' => 1
                ]);
                return redirect()->route('settings.email');
            }
            if ($this->request->action === "create_character_email") {
                if (is_null(Auth::user()->email()->first())) {
                    $validator = Validator::make($this->request->all(), [
                        'email_address' => "required|min:5|email|unique:user_emails,character_email",
                        'email_address_confirm' => "required|same:email_address",
                    ], [
                        'email_address.required' => "You do realize that the point of this is for you to give me an email address don't you, because I didn't get one. Try again please",
                        'email_address.min' => "I am not sure that it is possible for an email address to be that short. Try again, and make sure the address is at least :min characters long",
                        'email_address.unique' => "Sorry mate, that email address already exists in our database. Unfortunately, each character must have a unique email address.",
                        'email_address_confirm.required' => "I ask you to confirm the email address so that I know that I have the right address. You are failing miserably at that right now. Try again, and this time, type the address to confirm your email",
                        'email_address_confirm.same' => "I ask you to confirm the email address so that I know that I have the right address. You are failing miserably at that right now. Try again and this time, make sure the email addresses match."
                    ]);
                    if ($validator->fails()) {
                        return redirect()->route('settings.email')->withErrors($validator)->withInput();
                    }
                    Auth::user()->email()->save(
                        \EVEMail\UserEmail::create([
                            'character_email' => $this->request->get('email_address'),
                            'email_verification_code' => str_random(255)
                        ])
                    );

                    Mail::to($this->request->get('email_address'))->send(new EmailVerification(Auth::user()));
                    $this->request->session()->flash('alert', [
                        "header" => "Email Setting Updated Successfully",
                        'message' => "You have successfully updated your user record with an email address. Please check your inbox for a verification email from us. You will not be able to receive notifications at this email address until the verification process has been completed.",
                        'type' => 'info',
                        'close' => 1
                    ]);
                    return redirect()->route('settings.email');

                }
            }
        }
        return view('settings.email');
    }

    public function verify($vCode)
    {
        $validator = Validator::make(['vCode' => $vCode], [
            'vCode' => "required|size:255|string",

        ], [
            'vCode.required' => "You do realize that the point of this is for you to give me an email address don't you, because I didn't get one. Try again please",
            'vCode.size' => "Your verification code is not the right length, which means that it was probably trimed. Please try again, and if you copyed the URL in to the address bar, please try clicking the button in the URL.",
            'vCode.string' => "The vCode you submitted is invalid. Please delete the email address listed above and try again."
        ]);
        if ($validator->fails()) {
            return redirect()->route('settings.email')->withErrors($validator);
        }
        $update = Auth::user()->email()->where(['email_verification_code' => $vCode, 'verified' => 0])->update([
            'verified' => 1
        ]);
        if (!$update) {
            $this->request->session()->flash('alert', [
                "header" => "Houston, We have a probelm",
                'message' => "We were unable to update our record with  your verified email address. Please return to your inbox and try one more time. If the problem persits, please create an issue on GitHub",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('settings.email');
        }
        $this->request->session()->flash('alert', [
            "header" => "Email Verified Successfully",
            'message' => "Thank You for verifing your email address. Please proceed to your preferences now so that you can opt into the various features that are offered by EVEMail",
            'type' => 'info',
            'close' => 1
        ]);
        return redirect()->route('settings.email');
    }

    public function preferences ()
    {
        if ($this->request->isMethod('post')) {
            if ($this->request->has('preferences')) {
                //Verifed Valid Preferences Submiteed
                $preferences =[];
                foreach ($this->request->get('preferences') as $k=>$preference) {
                    $preferences[$k] = ($preference === "on") ? 1 : 0;
                }

                Auth::user()->update([
                    'preferences' => json_encode($preferences)
                ]);
            } else {
                Auth::user()->update([
                    'preferences' => null
                ]);
            }
            $this->request->session()->flash('alert', [
                "header" => "Preferences Updated Successfully",
                'message' => "Your preferences have been updated successsfully.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect()->route('settings.preferences');
        }
        if (!Auth::user()->email()->first()) {
            $this->request->session()->flash('alert', [
                "header" => "Invalid Page Request",
                'message' => "You must have an email address attached to your account before being about to access the Notification Preferences page. Please use this page to set and verify an email address",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect()->route('settings.email');
        }
        $preferences = json_decode(Auth::user()->preferences, true);
        return view('settings.preferences', ['preferences' => $preferences]);
    }

    public function construction()
    {
        return view ('settings.construction');
    }



}
