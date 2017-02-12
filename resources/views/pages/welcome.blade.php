@extends('bedrock.index')
@section('page_title', 'Welcome')

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
            <div class="col-lg-3">
                <h4>MailBoxes</h4>
                <ul class="list-group">
                    <li class="list-group-item">You do not have any mailbox at this time</li>
                </ul>
                <h4>Labels</h4>
                <ul class="list-group">
                    <li class="list-group-item">You do not have any labels at this time</li>
                </ul>
                <h4>Mailing Lists</h4>
                <ul class="list-group">
                    <li class="list-group-item">You are not on any mailing lists at this time.</li>
                </ul>
            </div>
            <div class="col-lg-9">
                <h2 class="page-header">Your Inbox</h2>
                <p>
                    Welcoem to EVEMail {{ Auth::user()->character_name }}. This area is generally filled with messages in your inbox, but since you are new to this site, we have not pull you Mail Box Data from EVE yet. This can take a while due to the amount of data that is pulled down, so when you're ready, please click the button below and sit back for a sec. We'll let you know when we are done. This is the only time that this will have to be manually down. From here on out this process is automated.
                </p>
                <div class="row">
                    <div class="col-md-4 col-md-offset-4">
                        <a href="{{ route('dashboard.welcome.download') }}" class="btn btn-primary btn-block">Download My Messages</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
