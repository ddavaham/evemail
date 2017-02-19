<div class="col-lg-3">
    <hr />
    <div class="list-group">
        <a href="{{ route('mail.send.build') }}" class="list-group-item">

            Send a Message
        </a>

    </div>
    <h4>MailBoxes</h4>
    <div class="list-group">
        <a href="{{ route('dashboard') }}" class="list-group-item">

            All Messages
        </a>

    </div>
    <hr />
    <div class="list-group">
        @foreach ($mail_labels as $label)

            <a href="{{ route('dashboard', ['label_id' => $label->label_id]) }}" class="list-group-item">
                <span class="badge">{{ $label->label_unread_count }}</span>
                {{ $label->label_name }}
            </a>
        @endforeach
    </div>
    <h4>Mailing Lists (Coming Soon)</h4>
    <div class="list-group">
        @if (count($mailing_lists)>0)
            @foreach ($mailing_lists as $list)
                <a href="" class="list-group-item">

                    {{ $list->recipient_name }}

                </a>
            @endforeach
        @else
            <li class="list-group-item">You are not subscribed to any mailing lists</li>
        @endif
    </div>
</div>
