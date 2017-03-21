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
                    <div class="pull-right">
                        <a href="{{ route('dashboard', ['label_id' => $label_id]) }}" class="btn btn-danger">Leave Multiedit Mode</a>
                    </div>

                    {{ Auth::user()->character_name }}'s Mailbox

                </h2>

                <ul class="list-group">
                    @if ($mail_headers->count() == 0)

                        <a href="#" class="list-group-item text-center">You don't have any mail in this mailbox.</a>
                    @else
                        <form action="{{ route('dashboard.multiedit.post', ['label_id' => $label_id]) }}" method="post">
                            @foreach ($mail_headers as $header)
                                <li class="list-group-item @if (!$header->is_read) list-group-item-info @endif">
                                    <div class="row">
                                        <div class="col-md-1 hidden-xs hidden-sm">
                                            <img src="{{ config('services.eve.img_serv') }}/Character/{{ $header->mail_sender }}_64.jpg" class="img-responsive img-rounded center-block" alt="Portriat of Something"  />
                                        </div>
                                        <div class="col-md-9 col-sm-12 col-xs-12">
                                            <h4 class="list-group-item-heading">{{ $header->mail_subject }}</h4>
                                            <p class="list-group-item-text">
                                                <em>Mail Sent by {{ $header->recipient_name }} on {{ \Carbon\Carbon::createFromTimestamp(strtotime($header->mail_sent_date))->toDayDateTimeString() }}</em>
                                            </p>
                                        </div>
                                        <div class="col-md-2 hidden-xs hidden-sm">
                                            <div class="btn-group">
                                                <label class="btn btn-default">
                                                    <input type="checkbox" name="multiedit[]" value="{{ $header->mail_id }}" autocomplete="off">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                            <li class="list-group-item">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-block">Delete Selected</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" name="action" value="read" class="btn btn-info btn-block">Mark as Unread/Read</button>
                                    </div>
                                </div>
                            </li>
                        </form>
                    @endif
                </ul>



            </div>
        </div>
    </div>

@endsection

@section('page_js')
<script>
    $('.select').on('click', function () {
        $(this).button('complete') // button text will be "finished!"
    })
</script>
@endsection
