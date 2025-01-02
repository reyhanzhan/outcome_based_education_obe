<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'layouts.app')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="vendor/fontawesome-free-6.5.1-web/css/all.min.css">
    <link rel="stylesheet" href="fonts/Poppins\Poppins.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="/img/logo-uwp1.png" type="image/x-icon">
    <style>
        * {
            outline: solid 1px green;
            outline: solid 1px transparent;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .page-header {
            z-index: 1000;
        }

        .page-header,
        .page-footer {
            flex-grow: 0;
        }

        .page-content {
            flex-grow: 1;
        }

        /* Efek Hover Dropdown Item */
        .dropdown-item,
        .btn-hover {
            transition: all 0.3s ease-in-out;
            /* Efek transisi halus */
        }

        /*button hover teks sidebar di mobile & menu header di desktop start */
        .dropdown-item:hover,
        .btn-hover:hover {
            color: white !important;
            /* Warna teks */
            background-color: #0052A2 !important;
            /* Warna latar belakang */
            transform: scale(1.05);
            /* Perbesar sedikit */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            /* Tambahkan bayangan */
            border-radius: 8px;
            /* Sedikit rounded untuk estetika */
        }

        /* Keyframes untuk animasi ikon */
        @keyframes bounceIcon {

            0%,
            40%,
            100% {
                transform: translateY(0);
                /* Tetap di tempat */
            }

            20% {
                transform: translateY(-5px);
                /* Melompat */
            }
        }

        @keyframes rotateIcon {

            0%,
            40%,
            100% {
                transform: rotate(0deg);
                /* Tetap di tempat */
            }

            20% {
                transform: rotate(360deg);
                /* Rotasi penuh */
            }
        }

        @keyframes scaleIcon {

            0%,
            40%,
            100% {
                transform: scale(1);
                /* Tetap di tempat */
            }

            20% {
                transform: scale(1.1);
                /* Membesar sedikit */
            }
        }

        /* Efek hover dengan jeda sebelum dan setelah gerak */
        .nav-item:hover .icon-hover {
            animation: bounceIcon 2s ease-in-out infinite;
            /* Beranda */
            animation-delay: 0.5s;
            /* Jeda sebelum mulai */
        }

        .nav-item:hover .icon-cpl {
            animation: rotateIcon 2s ease-in-out infinite;
            /* CPL */
            animation-delay: 0.5s;
        }

        .nav-item:hover .icon-pl {
            animation: scaleIcon 2s ease-in-out infinite;
            /* PL */
            animation-delay: 0.5s;
        }

        .nav-item:hover .icon-cpmk {
            animation: bounceIcon 2s ease-in-out infinite;
            /* CPMK */
            animation-delay: 0.5s;
        }

        .nav-item:hover .icon-bk {
            animation: rotateIcon 2s ease-in-out infinite;
            /* BK */
            animation-delay: 0.5s;
        }

        .nav-item:hover .icon-mk {
            animation: scaleIcon 2s ease-in-out infinite;
            /* MK */
            animation-delay: 0.5s;
        }

        .nav-item:hover .icon-pemetaan {
            animation: bounceIcon 2s ease-in-out infinite;
            /* Pemetaan */
            animation-delay: 0.5s;
        }

        /* Transisi halus saat hover start */
        .icon-hover,
        .icon-cpl,
        .icon-pl,
        .icon-cpmk,
        .icon-bk,
        .icon-mk,
        .icon-pemetaan {
            transition: transform 0.3s ease;
        }

        /* end Transisi halus saat hover */

        @media (max-width: 991.98px) {
            .dropdown-menu {
                position: relative !important;
                transform: none !important;
            }
        }

        @media (min-width: 992px) {
            .dropdown:hover>.dropdown-menu {
                display: block !important;
            }
        }

        /* Sembunyikan logo pada layar kecil */
        .navbar .navbar-brand {
            display: block;
        }

        @media (max-width: 991.98px) {
            .navbar .navbar-brand {
                display: none;
                /* Sembunyikan logo */
            }

            /* Tampilkan logo di sidebar */
            .offcanvas-header .sidebar-logo {
                display: block;
                /* Tampilkan logo */
            }
        }

        @media (min-width: 992px) {
            .offcanvas-header .sidebar-logo {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- ingat jangan ubah body, buat div wrapper  -->
    <!-- wrapper start -->
    <div class="page-wrapper"
        style="background-image: url('/img/texture.jpg'); background-repeat: repeat; min-height: 100vh;">

        <!-- page header start -->
        <header class="page-header sticky-top">
            <!-- navbar start -->
            <!-- background dengan pattern sudah di layouts_baru.app -->
            <nav class="navbar navbar-dark bg-primary py-3 py-lg-4"
                style="background: url('{{ asset('img/pat_04.png') }}') #004680 !important;">
                <div class="container column-gap-3">


                    <div class="flex-grow-0 d-lg-none">
                        <button class="navbar-toggler rounded-0 border-0 p-0" data-bs-toggle="offcanvas"
                            data-bs-target="#sidebar">
                            <i class="navbar-toggler-icon"></i>
                        </button>
                    </div>
                    {{-- logo & teks start --}}
                    <div class="flex-grow-0">
                        <a href="index.html" class="navbar-brand m-0">
                            {{-- <div class="ratio ratio-1x1 bg-white rounded-circle" style="width: 3rem;"></div> --}}
                            <img src="{{ asset('img/logo_obe_crop.png') }}" alt="Logo" class="img-fluid"
                                style="width: 20rem; height: auto;">
                        </a>
                    </div>
                    {{-- logo & teks end --}}

                    {{-- style profil start --}}
                    <style>
                        /* style menu profil Dropdown Menu Mengambang di Paling Atas start */
                        #menu-profil {
                            position: absolute !important;
                            /* Tetapkan posisi tetap di layar */
                            top: 40px;
                            /* Jarak dari bagian atas halaman */
                            right: 0;
                            /* Jarak dari sisi kanan halaman */
                            z-index: 9999 !important;
                            /* Muncul paling atas */
                            width: 200px;
                            /* Lebar dropdown */
                            /* height: 240px; */
                            background-color: #fff;
                            /* Latar belakang dropdown */
                            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
                            /* Bayangan dropdown */
                            border: none;
                            /* Hilangkan border */
                            border-radius: 0.5rem;
                            /* Border radius agar lebih estetik */
                            overflow: hidden;
                            /* Hindari elemen keluar */

                        }

                        /* end style menu profil Dropdown Menu Mengambang di Paling Atas */

                        /* Efek Hover untuk Dropdown Item */
                        .dropdown-item:hover {
                            background-color: #0052A2;
                            color: white !important;
                        }
                    </style>
                    {{-- style profile end --}}
                    <!-- Profil Start -->
                    <div class="flex-grow-1">
                        <ul class="navbar-nav justify-content-end flex-row">
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center text-white"
                                    id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle fs-5 me-2"></i>
                                    <span class="d-none d-lg-block">{{ Auth::user()->name }}</span>
                                </a>
                                <!-- Dropdown profil Menu start -->
                                <div class="dropdown-menu dropdown-menu-end shadow"
                                    aria-labelledby="profileDropdown" id="menu-profil" style="padding-top: 2">
                                    <div class="text-center">
                                        <div class="p-3 rounded-3 text-white" style="background: url('{{ asset('img/pat_04.png') }}') #004680 !important;">
                                            <img src="https://via.placeholder.com/80" alt="Profile Picture"
                                                class="rounded-circle mb-2" />
                                            <h6 class="fw-bold mb-0">{{ Auth::user()->name }}</h6>
                                            <small>{{ Auth::user()->jabatan }}</small><br>
                                            <small>{{ Auth::user()->prodi }}</small>

                                        </div>
                                        <div class="d-flex justify-content-around mt-2">
                                            <a href="/profile" class="btn btn-outline-primary btn-sm"><i
                                                    class="bi bi-person"></i> Profil</a>
                                            {{-- <a href="/menu" class="btn btn-outline-primary btn-sm"><i
                                                    class="bi bi-grid"></i> Menu</a> --}}
                                            {{-- logout start --}}
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                style="display: none;">
                                                @csrf
                                            </form>
                                            <a href="#" class="btn btn-outline-danger btn-sm"
                                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                <i class="bi bi-box-arrow-right"></i> Keluar
                                            </a>
                                            {{-- logout end --}}
                                        </div>
                                    </div>
                                </div>
                                {{-- dropdown menu profil end --}}
                            </li>
                        </ul>
                    </div>
                    <!-- Profil End -->
                </div>
            </nav>
            <!-- end navbar -->

            <!-- sidebar start-->
            <div class="offcanvas-lg offcanvas-start bg-light" data-bs-scroll="true" data-bs-backdrop="static"
                tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
                {{-- <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="sidebarLabel">Outcome Based Education</h5>
                    <button type="button" class="btn-close" data-bs-toggle="offcanvas"
                        data-bs-target="#sidebar"></button>
                </div> --}}
                <!-- Sidebar Header -->
                <div class="offcanvas-header" style="background: url('{{ asset('img/pat_04.png') }}') #004680 !important;">
                    {{-- <h5 class="offcanvas-title" id="sidebarLabel">Outcome Based Education</h5> --}}
                    <img src="{{ asset('img/logo_obe_crop.png') }}" alt="Logo" class="sidebar-logo img-fluid"
                        style="width: 300px;">
                    <button type="button" class="btn-close" data-bs-toggle="offcanvas"
                        data-bs-target="#sidebar"></button>
                </div>

                <div class="offcanvas-body px-0">
                    <div class="container-lg">
                        <ul class="nav flex-column flex-lg-row py-lg-2 gap-3">
                            <li class="nav-item">
                                <!-- atur jarak tiap tombol -->
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center text-dark btn-hover">
                                    <i class="fa fa-home me-2 icon-hover"></i> Beranda
                                </a>
                            </li>
                            <!-- button cpl start -->
                            <li class="nav-item dropdown">
                                <!-- data-bs-toogle = Fungsi:
                        Atribut ini memberi tahu Bootstrap bahwa elemen ini berfungsi untuk membuka dropdown menu(data-bs-toogle) -->
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-cpl">
                                    <i class="fa fa-graduation-cap me-2 icon-cpl"></i> CPL
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-cpl">
                                    <li><a href="/CPL/index" class="dropdown-item">Daftar CPL</a></li>
                                    <li><a href="/CPL/create" class="dropdown-item">Tambah CPL</a></li>
                                </ul>
                            </li>
                            <!-- button cpl end -->
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-pl">
                                    <i class="fa fa-user-graduate me-2 icon-pl"></i> PL
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-pl">
                                    <li><a href="/PL/index" class="dropdown-item">Daftar PL</a></li>
                                    <li><a href="/PL/create" class="dropdown-item">Tambah PL</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-cpmk">
                                    <i class="fa fa-book me-2 icon-cpmk"></i> CPMK
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-cpmk">
                                    <li><a href="/CPMK/index" class="dropdown-item">Daftar CPMK</a></li>
                                    <li><a href="/CPMK/create" class="dropdown-item">Tambah CPMK</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-bk">
                                    <i class="fa fa-book-open me-2 icon-bk"></i> BK
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-bk">
                                    <li><a href="/BK/index" class="dropdown-item">Daftar BK</a></li>
                                    <li><a href="/BK/create" class="dropdown-item">Tambah BK</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-mk">
                                    <i class="fa fa-bookmark me-2 icon-mk"></i> MK
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-mk">
                                    <li><a href="/MK/index" class="dropdown-item">Daftar MK</a></li>
                                    <li><a href="/MK/create" class="dropdown-item">Tambah MK</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-pemetaan">
                                    <i class="fa fa-map me-2 icon-pemetaan"></i> Pemetaan

                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-pemetaan">
                                    <li><a href="{{ route('pemetaan_CPL-PL.index') }}" class="dropdown-item">Pemetaan
                                            CPL-PL</a></li>
                                    <li><a href="{{ route('pemetaan_CPMK-CPL.index') }}"
                                            class="dropdown-item">Pemetaan
                                            CPMK-CPL</a></li>
                                    <li><a href="{{ route('pemetaan_CPMK-CPL-MK.index') }}"
                                            class="dropdown-item">Pemetaan
                                            CPMK-CPL-MK</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- end sidebar -->

        </header>
        <!-- end page header -->

        <!-- start page content -->
        <main class="page-content">
            <section>
                <div class="container">
                    @yield('content')
                    {{-- button modal/pop up start --}}
                    {{-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal">
                  OPEN MODAL
               </button> --}}
                    {{-- button modal/pop up end --}}
                </div>
            </section>
        </main>
        <!-- end page content -->

        <!-- start footer -->
        <footer class="page-footer" style="background-color: #004680; color:white">
            {{-- <div class="container py-4">
                copyright &copy;
                <script src="../../../../public/js/year.js"></script>
            </div> --}}
            <div class="container py-4">
                © {{ date('Y') }} <b>Outcome Based Education.</b> All Rights Reserved.
            </div>
        </footer>
        <!-- end page-footer -->

    </div>
    <!-- end page wrapper -->

    <!-- content modal start -->
    {{-- <div class="modal fade" id="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
         <div class="modal-content">
            <div class="modal-header">
               <h1 class="modal-title fs-5" id="modalLabel">Modal title</h1>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div> --}}
    <!-- content modal end-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>
