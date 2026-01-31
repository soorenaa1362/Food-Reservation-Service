<!DOCTYPE html>
<html lang="en" class="loading">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>@yield('title')</title>
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('convex/img/ico/apple-icon-60.html') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('convex/img/ico/apple-icon-76.html') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('convex/img/ico/apple-icon-120.html') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('convex/img/ico/apple-icon-152.html') }}">
        {{-- <link rel="shortcut icon" type="image/x-icon" href="https://pixinvent.com/demo/convex-bootstrap-admin-dashboard-template/app-assets/img/ico/favicon.ico"> --}}
        <link rel="shortcut icon" type="image/png" href="{{ asset('convex/img/ico/favicon-32.png') }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-touch-fullscreen" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <link href="convex/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/fonts/feather/style.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/fonts/simple-line-icons/style.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/fonts/font-awesome/css/font-awesome.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/perfect-scrollbar.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/prism.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/chartist.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/css/app.css') }}">
        <link href="{{ asset('custom/sidebar-disabled.css') }}" rel="stylesheet" type="text/css" />

        {{-- CSRF Token --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @yield('styles')
    </head>
    <body data-col="2-columns" class="2-columns" style="background: url('{{ asset('custom/img/foods-1.jpeg') }}') no-repeat center center fixed; background-size: cover;">
        <div class="wrapper">
            <div data-active-color="white" data-background-color="crystal-clear"
                data-image="{{ asset('convex/img/sidebar-bg/08.jpg') }}" class="app-sidebar">
                @include('layouts.sections.sidebar_header')
                @include('layouts.sections.sidebar_content')
                <div class="sidebar-background"></div>
            </div>
            @include('layouts.sections.navbar')
            <div class="main-panel">
                <div class="main-content">
                    {{-- <div class="content-wrapper"> --}}
                        {{-- <div class="container-fluid"> --}}
                            @yield('content')
                        {{-- </div> --}}
                    {{-- </div> --}}
                </div>
            </div>
        </div>
        @include('layouts.sections.notification_sidebar')
        @include('layouts.sections.settings')

        <script src="{{ asset('convex/vendors/js/core/jquery-3.3.1.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/core/popper.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/core/bootstrap.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/perfect-scrollbar.jquery.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/prism.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/jquery.matchHeight-min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/screenfull.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/pace/pace.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/chartist.min.js') }}"></script>
        <script src="{{ asset('convex/js/app-sidebar.js') }}"></script>
        <script src="{{ asset('convex/js/notification-sidebar.js') }}"></script>
        <script src="{{ asset('convex/js/customizer.js') }}"></script>
        <script src="{{ asset('convex/js/dashboard-ecommerce.js') }}"></script>
        @yield('scripts')
    </body>
</html>
