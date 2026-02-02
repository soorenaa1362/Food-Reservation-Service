<!DOCTYPE html>
<html lang="fa" dir="rtl" class="loading">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta name="description" content="سامانه رزرواسیون غذا - ورود آسان و سریع">
        <meta name="keywords" content="رزرو غذا, سامانه رزرواسیون, فرم ورود, رستوران">
        <meta name="author" content="PIXINVENT">
        <title>
            @yield('title')
        </title>
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('convex/img/ico/apple-icon-60.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('convex/img/ico/apple-icon-76.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('convex/img/ico/apple-icon-120.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('convex/img/ico/apple-icon-152.png') }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('convex/img/ico/favicon.ico') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('convex/img/ico/favicon-32.png') }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        {{-- <link href="https://fonts.googleapis.com/css2?family=Vazir:wght@300;400;500;700&display=swap" rel="stylesheet"> --}}
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/fonts/feather/style.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/fonts/simple-line-icons/style.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/fonts/font-awesome/css/font-awesome.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/perfect-scrollbar.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/vendors/css/prism.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('convex/css/app.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('custom/css/auth.css') }}"> 
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    {{-- <body data-col="1-column" class="1-column blank-page" style="background: url('{{ asset('custom/img/foods-1.jpeg') }}') no-repeat center center fixed; background-size: cover;"> --}}
        <div class="wrapper">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
        <script src="{{ asset('convex/vendors/js/core/jquery-3.3.1.min.js') }}"></script>
        <script src="{{ asset('convex/js/persian-datepicker.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/core/popper.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/core/bootstrap.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/perfect-scrollbar.jquery.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/prism.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/jquery.matchHeight-min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/screenfull.min.js') }}"></script>
        <script src="{{ asset('convex/vendors/js/pace/pace.min.js') }}"></script>
        <script src="{{ asset('convex/js/app-sidebar.js') }}"></script>
        <script src="{{ asset('convex/js/notification-sidebar.js') }}"></script>
        @yield('scripts')
    </body>
</html>