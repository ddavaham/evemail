<p>
    Hey there {{ $user->character_name }},<br /><br />
     Thank you for submitting your email address to the EVEMail System. Doing so has opened the door to a plethora of features that the EVEMail system offers. Before you get started though, please verify this email address by clicking on the button below.
</p>
<p>
     <a href="{{ route('settings.email.action', ['action' => 'verify', 'vCode' => $user->email()->first()->email_verification_code]) }}" target="_blank">Verify My Email Address</a>
</p>
<p>If you believe that you have received this email in error, please ignore it. We DO NOT SPAM and your email address will be deleted from our system by the end of the day UTC</p>
<p>Thank You,<br />EVEMail Admins</p>
