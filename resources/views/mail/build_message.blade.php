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
                <h2 class="page-header">Create Your Message</h2>
                @include('extra.alert')
                <div class="panel panel-default">
                    <form action="{{ route('mail.new', ['step_id' => 1]) }}" method="post">
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
                                    @if (Session::has('recipients'))
                                        @foreach (Session::get('recipients') as $k=>$recipient)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default">{{ $recipient->recipient_name }}</button>
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('mail.new', ['label_id' => 1, 'remove' => $k]) }}">Remove This Recipient</a></li>
                                            </ul>
                                        </div>
                                        @endforeach
                                    @endif

                                    <button class="btn btn-default" onclick="addressBook()" type="button">Open Address Book</button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Subject:
                                </td>
                                <td>
    								<div class="form-group">
    									<input type="text" name="subject" id="subject" class="form-control" value="@if(Session::has('mail.subject')){{Session::get('mail.subject')}}@else{{old('subject')}}@endif"/>
    								</div>
                                </td>
                            </tr>
                        </table>
                        <div class="panel-body">
                            <h4>CSPA Fee Notice:</h4>
                            <p>
                                Sending Mail to somebody who is not in your contacts? This system will automatically send along an approval to deduct no more than 10K ISK for your account to pay Concord for the CSPA Fee. In the event 10K is not enough, you will need to find another method to contact the recipient of your mail. At this time, this system does not have the functionality to raise this fee.
                            </p>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
    							<textarea name="body" id="body" class="form-control" rows="20">@if (Session::has('mail.body')){{ trim(Session::get('mail.body'), 'l') }}@else{{ old('body') }}@endif</textarea>
    							<span class="helper">* right now this is only plain text. I will be adding a basic WYSIWYG Editor soon</span>
    						</div>
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ route('mail.reset') }}" class="btn btn-danger btn-block">Reset</a>
                                </div>
                                <div class="col-md-6">
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-primary btn-block">Preview Message</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>


            </div>
        </div>
    </div>

@endsection

@section('page_js')

<script>
    function addressBook() {
        var openAddressBook = window.open("{{ route('mail.new.recipient') }}", "Address Book", "height="+ (screen.height * .5) +" ,width="+ (screen.width * .5 )+",top="+ (screen.height * .25) +",left="+ (screen.width * .25));
    }
</script>

@endsection
