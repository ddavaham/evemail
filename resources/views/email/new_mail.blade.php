<p>
    Hey there {{ $user->character_name }},<br /><br />
    You're receiving this email because you opted into receive email notifications when system detected that you have a new EVEMail in your inbox. Below we have listed the header information about the new EVEMail.
</p>
<table>
    <thead>
        <th>
            From:
        </th>
        <th>
            Subject
        </th>
        <th>
            Date/Time Sent
        </th>
    </thead>
    <tbody>
        @foreach ($mail_headers as $header)
            <tr>
                <td>
                    {{ $header->sender()->first()->recipient_name }}
                </td>
                <td>
                    {{ $header->mail_subject }}
                </td>
                <td>
                    {{ \Carbon\Carbon::createFromTimestamp(strtotime($header->mail_sent_date))->toDayDateTimeString(); }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<p>If you believe that you have received this email in error, please ignore it. We DO NOT SPAM and your email address will be deleted from our system by the end of the day UTC</p>
<p>Thank You,<br />EVEMail Admins</p>
