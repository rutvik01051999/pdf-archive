<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" loader="enable">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        {{ config('app.name', 'Laravel') }}
    </title>
    <meta name="Description" content="Sample Admin Dashboard">
    <meta name="Author" content="Super Admin">
    <meta name="keywords" content="admin">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/node-waves/waves.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/@simonwep/pickr/themes/nano.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css?v={{ time() }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css?v={{ time() }}">
    
    
    <meta http-equiv="imagetoolbar" content="no">
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link href="{{ asset('assets/css/styles.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Switcher -->
    @include('admin.layouts.partials.switcher')

    <!-- Loader -->
    <div id="loader" class="d-none"> <img src="{{ asset('assets/images/media/loader.svg') }}" alt=""> </div>

    <div class="page">
        <!-- Header -->
        @include('admin.layouts.partials.header')

        <!-- Sidebar -->
        @include('admin.layouts.partials.sidebar')

        {{-- Content --}}
        <div class="main-content app-content">
            <div class="container-fluid">

                <!-- Body Content -->
                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        @include('admin.layouts.partials.footer')
    </div>

    <!-- Scroll To Top -->
    <div class="scrollToTop"> <span class="arrow"><i class="ri-arrow-up-s-fill fs-20"></i></span> </div>
    <div id="responsive-overlay"></div>

    <!-- Color Switcher -->
    @include('admin.layouts.partials.color-picker')

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/js/sticky.js') }}"></script>
    <script src="{{ asset('assets/js/defaultmenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/simplebar.js') }}"></script>
    <script src="{{ asset('assets/libs/@simonwep/pickr/pickr.es5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/libs/chart.js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom-switcher.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/libs/moment.js/moment.min.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        const daterangeLocale = {
            "format": "YYYY-MM-DD",
            "customRangeLabel": "@lang('app.customRange')",
            "separator": "@lang('app.to')",
            "applyLabel": "@lang('app.apply')",
            "cancelLabel": "@lang('app.cancel')",
            "daysOfWeek": ["@lang('app.weeks.sun')", "@lang('app.weeks.mon')",
                "@lang('app.weeks.tue')",
                "@lang('app.weeks.wed')", "@lang('app.weeks.thu')", "@lang('app.weeks.fri')",
                "@lang('app.weeks.sat')"
            ],
            "monthNames": [
                "@lang('app.months.january')",
                "@lang('app.months.february')",
                "@lang('app.months.march')",
                "@lang('app.months.april')",
                "@lang('app.months.may')",
                "@lang('app.months.june')",
                "@lang('app.months.july')",
                "@lang('app.months.august')",
                "@lang('app.months.september')",
                "@lang('app.months.october')",
                "@lang('app.months.november')",
                "@lang('app.months.december')"
            ],
        };
    </script>

    <!-- Custom Js -->
    @stack('scripts')

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js?v={{ time() }}"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js?v={{ time() }}"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js?v={{ time() }}"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js?v={{ time() }}"></script>
    
    

    <!-- Session Monitor for Admin Panel -->
    <script src="{{ asset('assets/js/session-monitor.js') }}"></script>

</body>

</html>
