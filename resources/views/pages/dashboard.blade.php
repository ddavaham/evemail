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
                <h2 class="page-header">
                    {{ Auth::user()->character_name }}'s Mailbox

                </h2>

                <div class="list-group">
                    @if ($mail_headers->count() == 0)

                        <a href="#" class="list-group-item text-center">You don't have any mail in this mailbox.</a>
                    @else
                        @foreach ($mail_headers as $header)
                            <a href="{{ route('mail', ['mail_id' => $header->mail_id]) }}" class="list-group-item @if (!$header->is_read) list-group-item-info @endif">
                                <div class="row">
                                    <div class="col-md-1 hidden-xs hidden-sm">
                                        <img src="{{ config('services.eve.img_serv') }}/Character/{{ $header->mail_sender }}_64.jpg" class="img-responsive img-rounded center-block" alt="Portriat of Something"  />
                                    </div>
                                    <div class="col-md-11 col-sm-12 col-xs-12">
                                        <h4 class="list-group-item-heading">{{ $header->mail_subject }}</h4>
                                        <p class="list-group-item-text">
                                            <em>Mail Sent by {{ $header->recipient_name }} on {{ \Carbon\Carbon::createFromTimestamp(strtotime($header->mail_sent_date))->toDayDateTimeString() }}</em>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>



            </div>
        </div>
    </div>
@endsection
