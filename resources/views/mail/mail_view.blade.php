@extends('bedrock.index')
@section('page_title', 'Dashboard')

@section('navbar')
    @include('bedrock.navbar')
@endsection

@section('footer')
    @include('bedrock.footer')
@endsection

@section('content')
    <div class="row">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="page-header">Welcome to EVEMail {{ Auth::user()->character_name }}</h2>
                @include('extra.alert')
            </div>
        </div>
        <div class="row">
            @include ('extra.sidebar-nav')
            <div class="col-lg-9">
                <h2 class="page-header">{{ $header->mail_subject }}</h2>

                <div class="panel panel-default">
                    <table class="table table-bodered">
                        <tr>
                            <td width=15%>
                                From:
                            </td>
                            <td>
                                {{ $header->recipient_name }}

                            </td>
                        </tr>
                        <tr>
                            <td>
                                To:
                            </td>
                            <td>

                                @if(isset($mail_recipients))
                                    @foreach ($mail_recipients as $recipient_id=>$recipient)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{ $recipient->recipient_name }}&nbsp;&nbsp;&nbsp;<span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            @if($header->mail_sender !== $recipient_id)
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{ route('mail.reply.build', ['mail_id' => $header->mail_id, 'recipient_id' => $recipient_id, 'first_time' => 1]) }}">Reply To This Recipient</a></li>
                                                </ul>
                                             @endif
                                        </div>
                                    @endforeach
                                @else
                                    <strong>Unable to Parse Recipients at this time</strong>
                                @endif
                                <a href="{{ route('mail.forward', ['mail_id' => $header->mail_id]) }}" class="btn btn-default disabled">Forward This Mail (Coming Soon)</a>

                            </td>
                        </tr>
                        <tr>
                            <td>
                                Date Sent:
                            </td>
                            <td>

                                {{ \Carbon\Carbon::createFromTimestamp(strtotime($header->mail_sent_date))->toDayDateTimeString() }}
                            </td>
                        </tr>
                    </table>
                    <div class="panel-body">
                        {!! nl2br($body->mail_body) !!}
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ route('mail.unread', ['mail_id' => $header->mail_id]) }}" class="btn btn-info btn-block">Mark This Message Unread</a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('mail.reply.build', ['mail_id' => $header->mail_id, 'first_time' => 1]) }}" class="btn btn-primary btn-block @if($header->mail_sender == Auth::user()->character_id) disabled @endif">Reply To All</a>
                            </div>
                            <div class="col-md-4">
                                <!-- <a href="#" class="btn btn-danger btn-block">Delete This Message</a> -->
                                <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteMailModel">
                                    Delete this Mail
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="deleteMailModel" tabindex="-1" role="dialog" aria-labelledby="deleteMailModel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Delete Mail {{ $header->mail_subject }}</h4>
                </div>
                <div class="modal-body">
                    Please confirm that you would like to delete this message. This action is irreversible.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Nevermind</button>
                    <a href="{{ route('mail.delete', ['mail_id' => $header->mail_id]) }}" class="btn btn-primary">Yes, Delete this message</a>
                </div>
            </div>
        </div>
    </div>
@endsection
