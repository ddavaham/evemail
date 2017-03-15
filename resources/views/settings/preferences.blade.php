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
            <form action="{{ route('settings.preferences.post') }}" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="section-header">Email Notifications</h3>
                        @foreach (config('app.static_attributes.preferences.email') as $system_preferences)
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="preferences[{{ $system_preferences['value'] }}|checkbox]" @if(isset($preferences[$system_preferences['value']]))checked="checked"@endif /> {{ $system_preferences['name'] }}
                                </label><br />
                                 {{ $system_preferences['description'] }}
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <h3 class="section-header">Site Preferences</h3>
                        <div class="form-group">
                            <label for="dashboard_default_label">Default Mailbox on login</label>
                            <select name="preferences[dashboard_default_label|select]" id="dashboard_default_label" class="form-control">
                                <option value=""> --- Please Select a Label --- </option>
                                @if (!is_null($user_labels))
                                    @foreach ($user_labels as $label)

                                        <option value="{{ $label->label_id }}" @if(isset($preferences['dashboard_default_label'])&&$preferences['dashboard_default_label']==$label->label_id)selected="selected"@endif>{{ $label->label_name }}</option>
                                    @endforeach()
                                @else
                                    <option value=""> --- There are currently no labels available --- </option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <hr />
                        <div class="form-group">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-default center-block">Update Preferences</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- /.row -->
@endsection
