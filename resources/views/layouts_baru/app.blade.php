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

        /*button hover teks sidebar di mobile & menu header di desktop start */
        .dropdown-item:hover,
        .btn-hover:hover {
            color: white !important;
            /* Warna teks saat hover */
            background-color: #0052A2 !important;
            /* Warna latar belakang */
        }

        /*hover teks sidebar end*/

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
    </style>
</head>

<body>
    <!-- jangan ubah body, buat div wrapper  -->
    <!-- wrapper start -->
    <div class="page-wrapper">

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
                    {{-- logo $ teks end --}}

                    {{-- profil start --}}
                    <style>
                        /* Dropdown Menu Mengambang di Paling Atas */
                        #menu-profil {
                            position: fixed !important;
                            /* Tetapkan posisi tetap di layar */
                            top: 70px;
                            /* Jarak dari bagian atas halaman */
                            right: 20px;
                            /* Jarak dari sisi kanan halaman */
                            z-index: 9999 !important;
                            /* Muncul paling atas */
                            width: 250px;
                            /* Lebar dropdown */
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

                        /* Efek Hover untuk Dropdown Item */
                        .dropdown-item:hover {
                            background-color: #0052A2;
                            color: white !important;
                        }
                    </style>
                    <!-- Profil Start -->
                    <div class="flex-grow-1">
                        <ul class="navbar-nav justify-content-end flex-row">
                            <li class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center text-white"
                                    id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle fs-5 me-2"></i>
                                    <span class="d-none d-lg-block">Muhammad Reyhan Rizqi</span>
                                </a>
                                <!-- Dropdown profil Menu start -->
                                <div class="dropdown-menu dropdown-menu-end p-3 shadow"
                                    aria-labelledby="profileDropdown" id="menu-profil">
                                    <div class="text-center">
                                        <div class="bg-primary p-3 rounded-3 text-white">
                                            <img src="https://via.placeholder.com/80" alt="Profile Picture"
                                                class="rounded-circle mb-2" />
                                            <h6 class="fw-bold mb-0">MUHAMMAD REYHAN RIZQI</h6>
                                            <small>Mahasiswa Teknik Informatika</small>
                                        </div>
                                        <div class="d-flex justify-content-around mt-3">
                                            <a href="/profile" class="btn btn-outline-primary btn-sm"><i
                                                    class="bi bi-person"></i> Profil</a>
                                            <a href="/menu" class="btn btn-outline-primary btn-sm"><i
                                                    class="bi bi-grid"></i> Menu</a>
                                            <a href="/logout" class="btn btn-outline-danger btn-sm"><i
                                                    class="bi bi-box-arrow-right"></i> Keluar</a>
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
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="sidebarLabel">Outcome Based Education</h5>
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
                                    Beranda
                                </a>
                            </li>
                            <!-- button cpl start -->
                            <li class="nav-item dropdown">
                                <!-- data-bs-toogle = Fungsi:
                        Atribut ini memberi tahu Bootstrap bahwa elemen ini berfungsi untuk membuka dropdown menu(data-bs-toogle) -->
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-cpl">
                                    CPL
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
                                    PL
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-pl">
                                    <li><a href="/PL/create" class="dropdown-item">Daftar PL</a></li>
                                    <li><a href="/PL/create" class="dropdown-item">Tambah PL</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-cpmk">
                                    CPMK
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-cpmk">
                                    <li><a href="/CPMK/create" class="dropdown-item">Daftar CPMK</a></li>
                                    <li><a href="/CPMK/create" class="dropdown-item">Tambah CPMK</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-bk">
                                    BK
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-bk">
                                    <li><a href="/BK/create" class="dropdown-item">Daftar BK</a></li>
                                    <li><a href="/BK/create" class="dropdown-item">Tambah BK</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-mk">
                                    MK
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-mk">
                                    <li><a href="/MK/create" class="dropdown-item">Daftar MK</a></li>
                                    <li><a href="/MK/create" class="dropdown-item">Tambah MK</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#"
                                    class="btn btn-outline-primary rounded-2 border-0 w-100 text-start text-lg-center dropdown-toggle text-dark btn-hover"
                                    data-bs-toggle="dropdown" data-bs-target="#dropdown-pemetaan">
                                    Pemetaan
                                </a>
                                <ul class="dropdown-menu rounded-2" id="dropdown-pemetaan">
                                    <li><a href="{{ route('pemetaan_CPL-PL.index') }}" class="dropdown-item">Pemetaan
                                            CPL-PL</a></li>
                                    <li><a href="{{ route('pemetaan_CPMK-PL.index') }}" class="dropdown-item">Pemetaan
                                            CPMK-CPL</a></li>
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
                    <header class="title-page py-3 py-lg-4">
                        <h5 class="fs-1 mb-0">Daftar CPL</h5>
                        <p class="m-0">
                            Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                        </p>
                    </header>

                    <!-- Isi Content Section Start -->
                    <p>isi content terakhir, perbaiki struktur nya dulu</p>
                    <!-- End Isi Content Section -->

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
