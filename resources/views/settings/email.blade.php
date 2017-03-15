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
                <li>
                    <a href="{{ route('settings') }}">Settings</a>
                </li>
                <li class="active">My Email Address</li>
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
            <h2 class="section-header">Email Settings</h2>
            <p>
                Use this page to manage your email settings with the EVEMail System
            </p>
            @include('extra.alert')
            <div class="row">
                <div class="col-md-6">
                    <form action="{{ route('settings.email.post') }}" method="post">
                        <div class="form-group">
                            <label for="">Email Address:</label>
                            <input type="email" name="email_address" id="email_address" class="form-control" value="{{ old('email_address') }}" placeholder="johndoe@example.com" />
                        </div>
                        <div class="form-group">
                            <label for="">Confirm Email Address:</label>
                            <input type="email" name="email_address_confirm" id="email_address_confirm" class="form-control" value="{{ old('email_address_confirm') }}" placeholder="johndoe@example.com" />
                        </div>
                        <div class="form_group">
                            {{ csrf_field() }}
                            <button type="submit" name="action" value="create_character_email" class="btn btn-default">Add My Email Address</button>
                        </div>
                    </form>
                </div>

                @if (!is_null(Auth::user()->email()->first()))
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading text-center">
                                <h4>Email Address Currently on File</h4>
                            </div>
                            <table class="table table-bordered">
                                <tr>
                                    <td>
                                        {{ Auth::user()->email()->first()->character_email }}
                                    </td>
                                    <td>
                                        <a href="#" data-toggle="modal" data-target="#deleteEmailAddressModel"> Delete</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Status
                                    </td>
                                    <td>
                                        {{ (Auth::user()->email()->first()->verified) ? "Verified" : "Not Verified" }}
                                    </td>
                                </tr>
                                @if (!Auth::user()->email()->first()->verified)
                                <tr>
                                    <td colspan="2">
                                        <a href="{{ route('settings.email.action', ['action' => 'resend']) }}">Click here to have your verification code resent to you.</a>
                                    </td>

                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- /.row -->
    <!-- Modal -->
    <div class="modal fade" id="deleteEmailAddressModel" tabindex="-1" role="dialog" aria-labelledby="deleteEmailAddressModel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Delete Your Email Address Confirmation</h4>
                </div>
                <div class="modal-body">
                    Please confirm that you would like to delete your email address from our system. This will automatically unsubscribe you from all EVEMail System Communications and reset your email preferences. This action is irreversable.
                </div>
                <div class="modal-footer">
                    <form action="{{ route('settings.email.post') }}" method="post">
                        {{ csrf_field() }}
                        <button type="submit" name="action" value="delete_character_email" class="btn btn-danger">Yes, Delete My Email Address</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
