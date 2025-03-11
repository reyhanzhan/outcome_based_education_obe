<style>
    .sidebar-light .nav-sidebar .nav-link {
        font-family: 'Poppins', sans-serif !important;
        font-weight: 500 !important;
        /* Tebal */
        color: #222 !important;
        /* Warna hitam pekat */
        transition: all 0.3s ease-in-out;
    }

    /* Warna menu saat aktif */
    .sidebar-light .nav-sidebar .nav-link.active {
        color: white !important;
        /* Pastikan teks berubah putih */
        background-color: #004680 !important;
        /* Latar belakang biru */
        font-weight: 500 !important;
        /* Lebih tebal saat aktif */
    }

    /* Warna menu saat di-hover */
    .sidebar-light .nav-sidebar .nav-link:hover {
        color: rgb(0, 0, 0) !important;
        /* Pastikan teks berubah putih */
        background-color: #004680 !important;
    }

    /* ikon juga berubah warna saat aktif atau hover */
    .sidebar-light .nav-sidebar .nav-link.active i{
        color: rgb(255, 255, 255) !important;
    }
    .sidebar-light .nav-sidebar .nav-link:hover i {
        color: rgb(0, 0, 0)portant;
    }

    /* Warna submenu */
    .sidebar-light .nav-sidebar .nav-treeview .nav-link {
        font-weight: 500 !important;
        color: #333 !important;
        /* Warna abu-abu lebih gelap agar tetap terbaca */
    }

    /* Hover submenu */
    .sidebar-light .nav-sidebar .nav-treeview .nav-link:hover {
        color: white !important;
        background-color: #004680 !important;
    }

    /* Pastikan sidebar memiliki background putih di mode normal */
    .sidebar-light {
        background-color: #ffffff !important;
        /* Putih */
    }

    /* Warna parent menu saat salah satu child aktif */
    .nav-sidebar .nav-item.has-treeview.menu-open>.nav-link {
        background-color: #dcdcdc !important;
        /* Warna abu-abu */
        color: #222 !important;
        font-weight: bold;
    }

    /* Warna child menu saat aktif */
    .nav-sidebar .nav-treeview .nav-link.active {
        background-color: #004680 !important;
        color: white !important;
    }


    /* Pastikan sidebar tetap berwarna di mode mobile */
    @media (max-width: 768px) {
        .main-sidebar {
            background-color: #ffffff !important;
            /* Warna putih */
        }
    }

    /* Fix tambahan jika sidebar masih transparan */
    @media (max-width: 768px) {

        .main-sidebar,
        .sidebar {
            background-color: #ffffff !important;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            /* Tambahkan bayangan agar tidak terlalu flat */
        }
    }

    /* Pastikan teks tetap hitam di sidebar */
    .sidebar-light .nav-sidebar .nav-link {
        color: #222 !important;
    }

    /* Warna menu saat aktif */
    .sidebar-light .nav-sidebar .nav-link.active {
        color: white !important;
        background-color: #004680 !important;
        font-weight: 500 !important;
    }
</style>

{{-- <nav class="navbar navbar-dark bg-primary py-3 py-lg-4"
                style="background: url('{{ asset('img/pat_04.png') }}') #004680 !important;"> --}}

{{-- <aside class="main-sidebar sidebar-dark-custom elevation-4"> --}}
<aside class="main-sidebar sidebar-light elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link" style="background: url('{{ asset('img/pat_04.png') }}') #004680 !important;">
        <img src="{{ asset('img/logo_obe_crop.png') }}" alt="Logo" class="img-fluid"
            style="width: 20rem; height: auto;">

    </a>


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class=" mt-3 pb-0 mb-0 d-flex">
            {{-- <div class="image">
                <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Alexander Pierce</a>
            </div> --}}
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <!-- Manajemen Data -->
                <li
                    class="nav-item has-treeview {{ in_array(strtolower(request()->segment(1)), ['bk', 'mk', 'cpmk', 'subcpmk', 'pl', 'cpl']) ? 'menu-open' : '' }}">
                    <a href="#"
                        class="nav-link {{ in_array(strtolower(request()->segment(1)), ['bk', 'mk', 'cpmk', 'subcpmk', 'pl', 'cpl']) ? 'menu-open' : '' }}">
                        <i class="nav-icon fas fa-folder"></i>
                        <p>
                            Manajemen Data
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('pl.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'pl' ? 'active' : '' }}">
                                <i class="fas fa-user-graduate nav-icon"></i>
                                <p>Profil Lulusan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('cpl.list') }}"
                                class="nav-link {{ request()->path() == 'CPL/index' ? 'active' : '' }}">
                                <i class="fas fa-file-alt nav-icon"></i>
                                <p>Capaian Profil Lulusan</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('bk.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'bk' ? 'active' : '' }}">
                                <i class="fas fa-book nav-icon"></i>
                                <p>Bahan Kajian</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('cpmk.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'cpmk' ? 'active' : '' }}">
                                <i class="fas fa-tasks nav-icon"></i>
                                <p>Capaian Pembelajaran Mata Kuliah</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('mk.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'mk' ? 'active' : '' }}">
                                <i class="fas fa-book-open nav-icon"></i>
                                <p>Mata Kuliah</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Pemetaan -->
                <li
                    class="nav-item has-treeview {{ in_array(strtolower(request()->segment(1)), ['cpl-pl', 'cpl-mk', 'cpl-bk', 'cpmk-cpl', 'cpmk-mk', 'cpl-cpmk-mk']) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ in_array(strtolower(request()->segment(1)), ['cpl-pl', 'cpl-mk', 'cpl-bk', 'cpmk-cpl', 'cpmk-mk', 'cpl-cpmk-mk']) ? 'menu-open' : '' }}">
                        <i class="nav-icon fas fa-sitemap"></i>
                        <p>
                            Pemetaan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('Cpl_Pl.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'cpl-pl' ? 'active' : '' }}">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>PL - CPL</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('Cpl_Bk.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'cpl-bk' ? 'active' : '' }}">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPL - BK</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('Cpmk_Cpl.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'cpmk-cpl' ? 'active' : '' }}">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPL - CPMK</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('Cpmk_Mk.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'cpmk-mk' ? 'active' : '' }}">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPMK - MK</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('Cpl_Mk.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'cpl-mk' ? 'active' : '' }}">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>MK - CPL</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('CplCpmkMk.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'cpl-cpmk-mk' ? 'active' : '' }}">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPL - CPMK - MK</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Penilaian -->
                <li
                    class="nav-item has-treeview {{ in_array(strtolower(request()->segment(1)), ['pembobotan', 'nilai', 'penilaian']) || request()->is('penilaian/cpl') || request()->is('penilaian/cpmk/*') || request()->is('visualisasi/cpmk/*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ in_array(strtolower(request()->segment(1)), ['pembobotan', 'nilai', 'penilaian']) || request()->is('penilaian/cpl') || request()->is('penilaian/cpmk/*') || request()->is('visualisasi/cpmk/*') ? 'menu-open' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Penilaian
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('pembobotan.index') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'pembobotan' ? 'active' : '' }}">
                                <i class="fas fa-balance-scale nav-icon"></i>
                                <p>Pembobotan</p>
                            </a>
                        </li>
                    </ul>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('nilai.mahasiswa.choose') }}"
                                class="nav-link {{ strtolower(request()->segment(1)) == 'nilai' ? 'active' : '' }}">
                                <i class="fas fa-edit nav-icon"></i>
                                <p>Input Nilai Mahasiswa</p>
                            </a>
                        </li>
                    </ul>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('penilaian.cpmk.choose_mahasiswa') }}"
                                class="nav-link {{ request()->is('penilaian/cpmk/choose_mahasiswa') || request()->is('penilaian/cpmk/choose_mk/*') || request()->is('penilaian/cpmk/*') ? 'active' : '' }}">
                                <i class="fas fa-chart-pie nav-icon"></i>
                                <p>Penilaian CPMK</p>
                            </a>
                        </li>
                    </ul>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('visualisasi.cpmk.choose_mahasiswa') }}"
                                class="nav-link {{ request()->is('visualisasi/cpmk/choose_mahasiswa') || request()->is('visualisasi/cpmk/choose_mk/*') || request()->is('visualisasi/cpmk/radar/*') ? 'active' : '' }}">
                                <i class="fas fa-chart-line nav-icon"></i>
                                <p>Visualisasi Grafik Radar</p>
                            </a>
                        </li>
                    </ul>

                <li class="nav-item">
                    <a href="{{ route('penilaian.cpl.choose_mahasiswa') }}"
                        class="nav-link {{ request()->is('penilaian/cpl/*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar nav-icon"></i>
                        <p>Penilaian CPL</p>
                    </a>
                </li>
                </li>
            </ul>
        </nav>
    </div>
</aside>
