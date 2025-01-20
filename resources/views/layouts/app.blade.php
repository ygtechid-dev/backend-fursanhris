<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{ asset('assets/img/favicon.png') }}" type="image/png" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!--plugins-->
    @include('includes.style')
    <script src="{{ asset('assets/js/jquery-3.6.4.min.js') }}"></script>

    @stack('append-style')

    <title>@yield('title')</title>
</head>

<body>
    <!--wrapper-->
    {{-- @include('sweetalert::alert') --}}
    <div class="wrapper" id="wrapper-app">

        <!--sidebar wrapper -->
            @include('includes.sidebar')
        <!--end sidebar wrapper -->

        <!--start header -->
        @include('includes.header')
        <!--end header -->

        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                @yield('content')
            </div>
        </div>
        <!--end page wrapper -->

        <!--start overlay-->
        <div class="overlay toggle-icon"></div>
        <!--end overlay-->
        <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i
                class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->

            <footer class="page-footer">
                <p class="mb-0">Copyright © 2024. All right reserved.</p>
            </footer>
    </div>
    <!--end wrapper-->

    @include('includes.script')
    @stack('addon-script')

</body>

</html>
