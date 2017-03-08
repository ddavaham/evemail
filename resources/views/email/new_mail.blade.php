<html>
    <head>
        <style type="text/css">
            .tg  {border-collapse:collapse;border-spacing:0;border-color:#ccc;margin:0px auto;}
            .tg td{font-family:Arial, sans-serif;font-size:14px;padding:15px 10px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#fff;}
            .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:15px 10px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:#ccc;color:#333;background-color:#f0f0f0;}
            .tg .tg-baqh{text-align:center;vertical-align:top}
            .tg .tg-lqy6{text-align:right;vertical-align:top}
            .tg .tg-b7b8{background-color:#f9f9f9;vertical-align:top}
            .tg .tg-yw4l{vertical-align:top}
            @media screen and (max-width: 767px) {.tg {width: auto !important;}.tg col {width: auto !important;}.tg-wrap {overflow-x: auto;-webkit-overflow-scrolling: touch;margin: auto 0px;}}
        </style>
    </head>
    <body>
        <p>
            Hey there {{ $user->character_name }},<br /><br />
            You're receiving this email because you opted into receiving email notifications when our system detected that you have a new EVEMail in your inbox. Below we have listed the header information about the new EVEMail(s).
        </p>
        <div class="tg-wrap" style="overflow-x:auto; -webkit-overflow-scrolling:touch; margin:auto 0px">
            <table class="tg" style="border-collapse:collapse; border-spacing:0; border-color:#ccc; margin:0px auto">
              <tr>
                <th class="tg-baqh" colspan="3" style="font-family:Arial, sans-serif; font-size:14px; font-weight:normal; padding:15px 10px; border-style:solid; border-width:1px; overflow:hidden; word-break:normal; border-color:#ccc; color:#333; background-color:#f0f0f0; text-align:center; vertical-align:top" bgcolor="#f0f0f0" align="center">Your New EVEMails</th>
              </tr>
              <tr>
                <td class="tg-b7b8" style="font-family:Arial, sans-serif; font-size:14px; padding:15px 10px; border-style:solid; border-width:1px; overflow:hidden; word-break:normal; border-color:#ccc; color:#333; background-color:#f9f9f9; vertical-align:top" bgcolor="#f9f9f9">From:</td>
                <td class="tg-b7b8" style="font-family:Arial, sans-serif; font-size:14px; padding:15px 10px; border-style:solid; border-width:1px; overflow:hidden; word-break:normal; border-color:#ccc; color:#333; background-color:#f9f9f9; vertical-align:top" bgcolor="#f9f9f9">Subject</td>
                <td class="tg-b7b8" style="font-family:Arial, sans-serif; font-size:14px; padding:15px 10px; border-style:solid; border-width:1px; overflow:hidden; word-break:normal; border-color:#ccc; color:#333; background-color:#f9f9f9; vertical-align:top" bgcolor="#f9f9f9">Date/Time Sent (UTC)</td>
              </tr>
              @foreach ($mail_headers as $header)
                  <tr>
                    <td class="tg-yw4l" style="font-family:Arial, sans-serif; font-size:14px; padding:15px 10px; border-style:solid; border-width:1px; overflow:hidden; word-break:normal; border-color:#ccc; color:#333; background-color:#fff; vertical-align:top" bgcolor="#fff">{{ $header->sender()->first()->recipient_name }}</td>
                    <td class="tg-yw4l" style="font-family:Arial, sans-serif; font-size:14px; padding:15px 10px; border-style:solid; border-width:1px; overflow:hidden; word-break:normal; border-color:#ccc; color:#333; background-color:#fff; vertical-align:top" bgcolor="#fff">{{ $header->mail_subject }}</td>
                    <td class="tg-lqy6" style="font-family:Arial, sans-serif; font-size:14px; padding:15px 10px; border-style:solid; border-width:1px; overflow:hidden; word-break:normal; border-color:#ccc; color:#333; background-color:#fff; text-align:right; vertical-align:top" bgcolor="#fff" align="right">{{ \Carbon\Carbon::createFromTimestamp(strtotime($header->mail_sent_date))->toDayDateTimeString() }}</td>
                  </tr>
              @endforeach

            </table>
            <a href="{{ route('login') }}">Click Here to log into Your EVEMail Account</a> 
            <p>Thank You,<br />EVEMail Admins</p>
        </div>
    </body>
</html>
