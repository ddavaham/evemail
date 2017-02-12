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
            </div>
        </div>
        <div class="row">
            @include ('extra.sidebar-nav')
            <div class="col-lg-9">
                <h2 class="page-header">Preview Your Message</h2>
                @include('extra.alert')
                <div class="panel panel-default">
                    <form action="{{ route('mail.new', ['step_id' => 2]) }}" method="post">
                        <table class="table table-bodered">
                            <tr>
                                <td width=15%>
                                    From:
                                </td>
                                <td>
                                    {{ Auth::user()->character_name }}
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    To:
                                </td>
                                <td>
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
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Subject:
                                </td>
                                <td>
    								{{ Session::get('mail.subject') }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    CSPA Notice:
                                </td>
                                <td>
    							    Sending Mail to sombody who is not in your contacts? This system will automatically send along an approval to duduct no more than 10K ISK for  your account to pay Concord for the CSPA Fee. In the event 10K is not enough, you will need to find another method to contact the recipient of your mail. At this time, this system does not have the functionality  to raise this fee.
                                </td>
                            </tr>
                        </table>
                        <div class="panel-body">

    						{!! nl2br(Session::get('mail.body')) !!}
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ route('mail.reply', ['step_id' => 1, 'mail_id' => $header->mail_id]) }}" class="btn btn-warning btn-block">Edit This Message</a>
                                </div>
                                <div class="col-md-6">
                                    {{ csrf_field() }}
                                    <a href="{{ route('mail.reply', ['step_id' => 3, 'mail_id' => $header->mail_id]) }}" class="btn btn-success btn-block">Send Message</a>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>


            </div>
        </div>
    </div>

@endsection
