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

@endsection
