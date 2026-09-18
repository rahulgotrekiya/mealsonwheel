<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light"
    data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Dashboard') | Meals on Wheels</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/panel/images/global/favicon-dark.svg') }}">

    {{-- Reads the data-* attributes above before first paint, so the panel does not flash. --}}
    <script src="{{ asset('assets/panel/js/layout.js') }}"></script>

    <link href="{{ asset('assets/panel/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/panel/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/panel/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/panel/css/custom.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/panel/css/custom.css') }}" rel="stylesheet">

    @stack('styles')
</head>

<body>
    <div id="layout-wrapper">
        @include('partials.panel.topbar')
        @include('partials.panel.sidebar')

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @include('partials.panel.page-title', ['title' => trim($__env->yieldContent('title', 'Dashboard'))])
                    @include('partials.panel.flash')

                    @yield('content')
                </div>
            </div>

            @include('partials.panel.footer')
        </div>
    </div>

    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>

    <script src="{{ asset('assets/panel/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/panel/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/panel/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/panel/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/panel/js/plugins.js') }}"></script>
    <script src="{{ asset('assets/panel/js/app.js') }}"></script>

    @stack('scripts')
</body>

</html>
