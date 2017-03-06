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
                <li><a href="{{ route('dashboard') }}">Home</a>
                </li>
                <li class="active">My Settings</li>
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
            <h2 class="section-header">Settings Overview</h2>

        </div>
    </div>
    <!-- /.row -->
    <!-- <div class="row">

        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-default panel-padding">
                <div class="panel-heading text-center">
                    <div class="panel-title">
                        My Settings
                    </div>
                </div>
                @include('extra.alert')
                <div class="list-group">
                    <a href="{{ route('settings.labels') }}" class="list-group-item">
                        <h4 class="list-group-item-heading">Update My Labels</h4>
                        <p class="list-group-item-text">
                            Do you have labels missing or did you update your lables in game. These labels are not dynamically pulled. They must be updated manually. Click here and we will download them again.
                        </p>
                    </a>
                    <a href="{{ route('settings.mailing_lists') }}" class="list-group-item">
                        <h4 class="list-group-item-heading">Update My Mailing Lists</h4>
                        <p class="list-group-item-text">
                            Do you have mailing lists missing or did you update your mailing lists in game. Mailing lists are not dynamically pulled. They must be updated manually. Click here and we will download them again.
                        </p>
                    </a>
                </div>
                <div class="panel-body">

                    <form action="{{ route('settings.post') }}" method="post">
                        <h4>Email Address Notifications</h4>
                        <p>
                            The following allow EVEMail to send you notifications about new messages that you have awaiting your attention in your inbox. If you would like to receiving these notification, please opt into the notifications and then supply an email address for us to send the notification too.
                        </p>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="notification_email" id="notification_email" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Email Address Confirm</label>
                            <input type="email" name="notification_email_confirm" id="notification_email" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label for="new_mail_notification">
                                <input type="checkbox" name="new_mail_notifications" id="new_mail_notifications"/>&nbsp;&nbsp;&nbsp; I want to receive new mail notifications from EVEMail when I get a new mail in my EVE Inbox
                            </label>


                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Save My Settings</button>
                        </div>
                    </form>
                </div>



            </div>
        </div>
    </div> -->
@endsection
