@if(Session::has('alert'))
    <div class="alert alert-dismissable alert-{{ Session::get('alert.type') }}">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        @if(Session::has('alert.header'))
            <h3>{{ Session::get('alert.header') }}</h3>
        @endif
        @if(Session::has('alert.message'))
            <p>{!! Session::get('alert.message') !!}</p>
        @endif
    </div>
@endif
@if (count($errors)>0)
    <div class="alert alert-danger">
        <h4>Not all requirements have been met. Please review the errors below and try again.</h4>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
