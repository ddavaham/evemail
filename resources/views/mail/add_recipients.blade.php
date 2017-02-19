@extends('bedrock.index')
@section('page_title', 'Dashboard')


@section('footer')
    @include('bedrock.footer')
@endsection

@section('content')
    <div class="row">

        <div class="col-lg-12">
            <h2 class="page-header text-center">Search Address Book</h2>

            <div class="panel panel-default">
                <form action="{{ route('mail.send.recipient') }}" method="post">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                @include('extra.alert')
                                <p>
                                    This method first searches our local database of Characters. If we are unable to locate your search term, we then reach out to CCP to see if we can find a match with CCP and deliver the results for you. Each of the results that we get back, we store locally so that they can be more easily searched next time.
                                </p>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="recipient_name">Recipient Name</label>
                                    <input type="text" name="recipient_name" id="recipient_name" class="form-control" value="{{ old('recipient_name') }}"/>
                                </div>
                            </div>
                        </div>
                        @if(Session::has('recipients'))
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="page-header">Selected Recipients</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="list-group">

                                    @if (Session::has('recipients'))
                                        @foreach (Session::get('recipients') as $k=>$recipient)
                                            <li class="list-group-item">{{ $recipient->recipient_name }}</li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>

                        </div>
                        @endif
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-sm-6 pull-right">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-primary btn-block">Search Recipient</button>
                            </div>
                            @if (Session::has('recipients'))
                            <div class="col-sm-6">

                                <button type="button" onclick="window.close()" class="btn btn-default btn-block">Close</button>
                            </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            @if (isset($results))
                <!-- Modal -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title text-center">Search Results (Click to select)</h4>
                    </div>
                    <form action="{{ route('mail.send.recipient.post',  ['recipient_id' => 2]) }}" method="post">
                        @if (!empty($results))
                            <div class="list-group">
                                @foreach ($results as $result)
                                    <a href="{{ route('mail.send.recipient', ['recipient_id' => $result->recipient_id]) }}" class="list-group-item">
                                        {{ $result->recipient_name }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <ul class="list-group">
                                <li class="list-group-item">Your search return 0 results. Help us out, and click the blue button below so that you results can be loaded into our database. Thank You!</li>
                            </ul>
                        @endif
                        {{ csrf_field() }}
                        <div class="panel-footer">
                            <input type="hidden" name="recipient_name" value="{{ request('recipient_name') }}" />
                            @if (Session::has('recipients'))
                            <div class="pull-right">
                                <button type="submit" name="search" value="local" class="btn btn-primary">Close Address Book</button>
                            </div>
                            @endif
                            <button type="submit" name="search" value="ccp" class="btn btn-info" data-toggle="tooltip" data-placement="left" title="This might text a couple of seconds. Please wait....">Search More...</button>
                        </div>
                    </form>


                </div>
            @endif

        </div>

    </div>

@endsection

@section ('page_js')
<script>
    window.onunload = refreshParent;
    function refreshParent() {
        window.opener.location.reload();
    }
</script>
@endsection
