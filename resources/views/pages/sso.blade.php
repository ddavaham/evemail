@extends('bedrock.index')
@section('page_title', 'Secure Login')

@section('navbar')
    @include('bedrock.navbar')
@endsection

@section('footer')
    @include('bedrock.footer')
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-default panel-padding">
                <div class="panel-heading text-center">
                    <div class="panel-title">
                        Secure SSO Login
                    </div>
                </div>
                <div class="panel-body">
                    @include('extra.alert')
                    <p>
                        Welcome. Login to access your EVE Mail Inbox
                    </p>
                    <a href="{{ $ssoUrl }}">
                        <img src="https://images.contentful.com/idjq7aai9ylm/4PTzeiAshqiM8osU2giO0Y/5cc4cb60bac52422da2e45db87b6819c/EVE_SSO_Login_Buttons_Large_White.png?w=270&h=45" class="img-responsive center-block" />
                    </a>

                </div>
            </div>
        </div>
    </div>
@endsection
