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
                <h2 class="page-header">The Who, The What, and The Why of EVEMail</h2>

            </div>
        </div>
		<div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h3 class="page-header">What is EVEMail</h3>
				<p>
					EVEMail is system that is designed to make it easy for you to stay in touch with members of a community that so many of us have come to love and enjoy.  It allows you to seamlessly login and check your mail and easily reply to that mail knowing full well that that response will be delivered to that recipient.
				</p>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h3 class="page-header">Why EVEMails</h3>
				<p>
					EVEMail was chosen because the developer knew beyond a shadow of a doubt that he could create a reliable system that people would enjoy using and find comfort in, knowing that the Mail that was being transmitted via his system was secure, was unsaleable, and uncompromising.
				</p>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h3 class="page-header">About the Developer</h3>
				<p>
					EVEMail was created by David Davaham. An industrialist at heart, David spends most of his time write code and developing websites. EVEMail is David’s first website publish on the internet for public use. In real life, David works in the Information Technology Field as a Service Desk Specialist on the Internal Service Desk for his company. EVEMail was built in his free time between calls and when not working on schoolwork.
				</p>
            </div>
        </div>
		<div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <h3 class="page-header">Would you like to know more?</h3>
				<p>
					EVEmail's Code is publicaly available and constantly updated. View it yourself by visiting our <a href="https://github.com/evemail/evemail">Github Page</a>.

					<!-- Stay update on the changes going on within the EVEMail system by subscibing to our mailing list in game (Not actually. I haven't made it yet) -->
				</p>

            </div>
        </div>

    </div>
@endsection
