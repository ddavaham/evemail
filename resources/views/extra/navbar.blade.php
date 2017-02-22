<div class="container">

    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="{{ route('home') }}">EVEMail</a>
    </div>

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
            <li>
                <a href="{{-- route('about') --}}">About</a>
            </li>
            <li>
                <a href="{{-- route('services') --}}">Services</a>
            </li>
            <li> 
                <a href="{{-- route('contact') --}}">Contact</a>
            </li>
        </ul>
    </div>

</div>
