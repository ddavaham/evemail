<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ route('home') }}">EVEMail</a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li>
                    <a href="{{ route('about') }}">About</a>
                </li>
                <li>
                    <a href="{{ route('services') }}">Services</a>
                </li>
                <li>
                    <a href="{{-- route('contact') --}}">Contact</a>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                @if(!Auth::check())
                <li>
                    <a href="{{ route('login') }}">Member Login</a>
                </li>
                @endif
                @if(Auth::check())
                <li>
                    <a href="{{ route('dashboard') }}">My Mail</a>
                </li>
                <li>
                    <a href="{{ route('settings') }}">My Settings</a>
                </li>

                <li>
                    <a href="{{ route('logout') }}">Logout</a>
                </li>
                @endif
            </ul>

        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
</nav>
