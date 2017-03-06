@extends('bedrock.index')
@section('page_title', 'My Settings')

@section('navbar')
    @include('bedrock.navbar')
@endsection

@section('footer')
    @include('bedrock.footer')
@endsection

@section('content')
    <!-- Page Heading/Breadcrumbs -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">My Settings
                <small>You have the control</small>
            </h1>
            <ol class="breadcrumb">
                <li>
                    <a href="{{ route('dashboard') }}">Home</a>
                </li>
                <li class="active">My Preferences</li>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <!-- Content Row -->
    <div class="row">
        <!-- Sidebar Column -->
        <div class="col-md-3">
            @include('extra.settings_nav')
        </div>
        <!-- Content Column -->
        <div class="col-md-9">
            <h2 class="section-header">User Preferences</h2>
            <p>
                Use this page to manage your email settings with the EVEMail System
            </p>
            @include('extra.alert')
            <div class="row">
                <div class="col-md-8">
                    <form action="{{ route('settings.preferences.post') }}" method="post">
                        @foreach (config('app.static_attributes.preferences') as $system_preferences)
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="preferences[{{ $system_preferences['value'] }}]" @if(isset($preferences[$system_preferences['value']]))checked="checked"@endif /> {{ $system_preferences['name'] }}
                                </label><br />
                                 {{ $system_preferences['description'] }}
                            </div>
                        @endforeach
                        <div class="form-group">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-default">Update Preferences</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /.row -->
@endsection
