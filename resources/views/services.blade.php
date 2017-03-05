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
                <h2 class="page-header">EVEMail Services: What is so great about this system?</h2>
				<p>
					EVEmail is a very simple, easy service to use. Read below for more information.
				</p>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h3 class="page-header">EVEMail Synchronization</h3>
				<p>
                    Using the ESI API from CCP, EVEMail is able to keep you up-to-date with the on going is EVE Online via your EVEMails. What does this really mean though? When you log into EVEMail, a unique token ties you account on EVEMail with you Character in EVE Online. Using this unique token, EVEMail Servers make periodic requests to CCP to see if you inbox has any unread and/or new mail. If you do, we update our database. That is it. This prevent you from logging into an inbox of 20+ unread EVEMails. You can log in periodically throughout the day, respond to unread mails, and then log out knowing that business is handled.
				</p>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h3 class="page-header">Unread/New Mail Notification</h3>
				<p>
					EVEMail features and Email Notification system that will ping you when we detect that you have a new mail in your inbox. Using the magic of the internet, you can opt into having this notification along with a specialized one time authorization link emailed to you. You open the mail in you email inbox, and read the message. If you would like to tell the system that you acknowledge the message and mark it as read, you can click on the link and by routed directly to the that message in your inbox and the mail will be marked as read, both locally and in EVEOnline. From there you can delete or update the mail.
				</p>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <hr>
				<p>
					Right now, these are all of the features that EVEMail update, but there are pleanty more on the way. Please stay tune for the last updates to EVEOnline by following our forum thread on the EVEOnline Forums, linked below, and if you are a code monkey, you can check out our Github repo, also linked below.
				</p>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="row">
                    <div class="col-md-6">
                        <a href="https://forums.eveonline.com/default.aspx?g=posts&t=511993" class="btn btn-primary btn-block">EVEOnline Forum Thread</a>
                    </div>
                    <div class="col-md-6">
                        <a href="https://github.com/evemail/evemail" class="btn btn-primary btn-block">GitHub Repo</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
