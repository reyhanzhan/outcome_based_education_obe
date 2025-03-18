<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AdminLTE Dashboard')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/img/logo-uwp1.png') }}">

    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('lte/plugins/fontawesome-free/css/all.min.css') }}">

    <!-- DataTables Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

    <!-- AdminLTE CSS (Pastikan Versi Sesuai dengan JS) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="vendor/fontawesome-free-6.5.1-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="fonts/Poppins\Poppins.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Bootstrap Select CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css">




    @yield('css') <!-- Untuk tambahan CSS di halaman lain -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

</head>


<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        @include('partials.navbar') <!-- Navbar -->
        @include('partials.sidebar') <!-- Sidebar -->

        <!-- Content Wrapper -->
        <div class="content-wrapper">

            <!-- Content Header (Breadcrumb & Title) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            {{-- <h3>@yield('title')</h3> --}}
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('mk.index') }}">Home</a></li>
                                <li class="breadcrumb-item active">@yield('title')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main Content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content') <!-- Konten halaman lain -->
                </div>
            </section>

        </div>
        

        @include('partials.footer') <!-- Footer -->

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI (dibutuhkan oleh AdminLTE) -->
    {{-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script> --}}
    <!-- Bootstrap Bundle dengan Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables & Buttons JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap Select JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

    





    <!-- AdminLTE JS jgan diubah-->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @yield('scripts') <!-- Untuk script tambahan di halaman lain -->

    <script>
        $(document).ready(function() {
            @if (Session::has('success'))
                toastr.success("{{ Session::get('success') }}");
            @endif

            @if (Session::has('error'))
                toastr.error("{{ Session::get('error') }}");
            @endif

            @if (Session::has('warning'))
                toastr.warning("{{ Session::get('warning') }}");
            @endif

            @if (Session::has('info'))
                toastr.info("{{ Session::get('info') }}");
            @endif
        });
    </script>

    @section('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let sidebarState = JSON.parse(sessionStorage.getItem("sidebarState")) || {};

                // Aktifkan kembali menu yang terbuka setelah reload
                $(".nav-item.has-treeview").each(function() {
                    let menuId = $(this).attr("data-id");
                    if (sidebarState[menuId]) {
                        $(this).addClass("menu-open");
                        $(this).find("> a").addClass("active");
                    }
                });

                // Toggle state menu saat diklik
                $(".nav-item.has-treeview > a").on("click", function(event) {
                    event.preventDefault(); // Hindari reload saat klik menu
                    let parent = $(this).parent();
                    let menuId = parent.attr("data-id");

                    // Toggle menu terbuka / tertutup
                    if (parent.hasClass("menu-open")) {
                        parent.removeClass("menu-open");
                        sidebarState[menuId] = false;
                    } else {
                        parent.addClass("menu-open");
                        sidebarState[menuId] = true;
                    }

                    // Simpan status menu di sessionStorage
                    sessionStorage.setItem("sidebarState", JSON.stringify(sidebarState));
                });

                // Pastikan sub-menu yang aktif juga tetap terbuka
                $(".nav-link.active").each(function() {
                    let closestTreeview = $(this).closest(".nav-item.has-treeview");
                    if (closestTreeview.length) {
                        closestTreeview.addClass("menu-open");
                        let menuId = closestTreeview.attr("data-id");
                        sidebarState[menuId] = true;
                        sessionStorage.setItem("sidebarState", JSON.stringify(sidebarState));
                    }
                });
            });
        </script>
    @endsection


    @section('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let sidebarState = JSON.parse(sessionStorage.getItem("sidebarState")) || {};

                // Pastikan AdminLTE tidak menutup menu yang seharusnya terbuka
                $(".nav-item.has-treeview").each(function() {
                    let menuId = $(this).attr("data-id");
                    if (sidebarState[menuId]) {
                        $(this).addClass("menu-open");
                        $(this).find("> a").addClass("active");
                    }
                });

                // AdminLTE punya event "collapsed.lte.pushmenu" yang bisa menutup menu, kita cegah itu
                $(document).on('collapsed.lte.pushmenu', function() {
                    sessionStorage.setItem("sidebarState", JSON.stringify({}));
                });

                // Toggle state menu saat diklik
                $(".nav-item.has-treeview > a").on("click", function(event) {
                    event.preventDefault();
                    let parent = $(this).parent();
                    let menuId = parent.attr("data-id");

                    if (parent.hasClass("menu-open")) {
                        parent.removeClass("menu-open");
                        sidebarState[menuId] = false;
                    } else {
                        parent.addClass("menu-open");
                        sidebarState[menuId] = true;
                    }

                    sessionStorage.setItem("sidebarState", JSON.stringify(sidebarState));
                });

                // Pastikan sub-menu yang aktif juga tetap terbuka
                $(".nav-link.active").each(function() {
                    let closestTreeview = $(this).closest(".nav-item.has-treeview");
                    if (closestTreeview.length) {
                        closestTreeview.addClass("menu-open");
                        let menuId = closestTreeview.attr("data-id");
                        sidebarState[menuId] = true;
                        sessionStorage.setItem("sidebarState", JSON.stringify(sidebarState));
                    }
                });
            });
        </script>
    @endsection

    @section('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                $('.nav-item.has-treeview > a').on('click', function(e) {
                    e.preventDefault();
                    let parent = $(this).parent();
                    if (parent.hasClass('menu-open')) {
                        parent.removeClass('menu-open');
                    } else {
                        $('.nav-item.has-treeview').removeClass(
                        'menu-open'); // Tutup semua sebelum buka yang diklik
                        parent.addClass('menu-open');
                    }
                });
            });
        </script>
    @endsection
</body>

</html>

<style>
    * {
        outline: solid 1px green;
        outline: solid 1px transparent;
        /* max-width: 100% !important; */
    }

    html,
    body {
        overflow-x: hidden !important;
        width: 100%;
    }
    
</style>
