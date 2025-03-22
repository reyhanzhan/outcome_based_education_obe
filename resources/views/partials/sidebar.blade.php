<style>
    /* Gaya dasar untuk semua nav-link di sidebar */
    .sidebar-light .nav-sidebar .nav-link {
        font-family: 'Poppins', sans-serif !important;
        font-weight: 500 !important;
        color: #222 !important; /* Warna hitam pekat untuk teks */
        transition: all 0.3s ease-in-out;
    }

    /* Ikon default */
    .sidebar-light .nav-sidebar .nav-link i {
        color: #222 !important; /* Ikon sama dengan teks */
    }

    /* Warna menu saat aktif (parent dan child) */
    .sidebar-light .nav-sidebar .nav-link.active {
        color: white !important; /* Teks putih */
        background-color: #004680 !important; /* Latar belakang biru */
        font-weight: 500 !important;
    }

    /* Ikon saat menu aktif */
    .sidebar-light .nav-sidebar .nav-link.active i {
        color: white !important; /* Ikon putih saat aktif */
    }

    /* Warna menu saat di-hover */
    .sidebar-light .nav-sidebar .nav-link:hover {
        color: white !important; /* Teks putih saat hover */
        background-color: #004680 !important; /* Latar belakang biru */
    }

    /* Ikon saat hover */
    .sidebar-light .nav-sidebar .nav-link:hover i {
        color: white !important; /* Ikon putih saat hover */
    }

    /* Gaya submenu */
    .sidebar-light .nav-sidebar .nav-treeview .nav-link {
        font-weight: 500 !important;
        color: #333 !important; /* Abu-abu gelap untuk submenu */
    }

    /* Hover submenu */
    .sidebar-light .nav-sidebar .nav-treeview .nav-link:hover {
        color: white !important;
        background-color: #004680 !important;
    }

    /* Ikon submenu saat hover */
    .sidebar-light .nav-sidebar .nav-treeview .nav-link:hover i {
        color: white !important;
    }

    /* Submenu saat aktif */
    .nav-sidebar .nav-treeview .nav-link.active {
        background-color: #004680 !important;
        color: white !important;
    }

    /* Ikon submenu saat aktif */
    .nav-sidebar .nav-treeview .nav-link.active i {
        color: white !important;
    }

    /* Warna parent menu saat salah satu child aktif */
    .nav-sidebar .nav-item.has-treeview.menu-open > .nav-link {
        background-color: #dcdcdc !important; /* Abu-abu */
        color: #222 !important;
        font-weight: bold;
    }

    /* Ikon parent menu saat menu terbuka */
    .nav-sidebar .nav-item.has-treeview.menu-open > .nav-link i {
        color: #222 !important;
    }

    /* Pastikan sidebar memiliki background putih */
    .sidebar-light {
        background-color: #ffffff !important;
    }

    /* Gaya sidebar di mode mobile */
    @media (max-width: 768px) {
        .main-sidebar,
        .sidebar {
            background-color: #ffffff !important;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
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
    <div class="sidebar" id="sidebar">
        
        <!-- Sidebar user panel -->
        <div class=" mt-3 pb-0 mb-0 d-flex">

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
                @if (Auth::check() && Auth::user()->role === 'kps')
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
                        <a href="#"
                            class="nav-link {{ in_array(strtolower(request()->segment(1)), ['cpl-pl', 'cpl-mk', 'cpl-bk', 'cpmk-cpl', 'cpmk-mk', 'cpl-cpmk-mk']) ? 'menu-open' : '' }}">
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
                @endif

                <!-- Penilaian -->
                @if (Auth::check() && in_array(Auth::user()->role, ['dosen', 'kps']))
                    <li
                        class="nav-item has-treeview {{ in_array(strtolower(request()->segment(1)), ['pembobotan', 'nilai', 'penilaian']) ||
                        request()->is('penilaian/cpl') ||
                        request()->is('penilaian/cpmk/*') ||
                        request()->is('visualisasi/cpmk/*') ||
                        request()->is('nilai*') ||
                        request()->is('penilaian*')
                            ? 'menu-open'
                            : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(strtolower(request()->segment(1)), ['pembobotan', 'nilai', 'penilaian']) ||
                            request()->is('penilaian/cpl') ||
                            request()->is('penilaian/cpmk/*') ||
                            request()->is('visualisasi/cpmk/*') ||
                            request()->is('nilai*') ||
                            request()->is('penilaian*')
                                ? 'active'
                                : '' }}">
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
                                <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}"
                                    class="nav-link {{ request()->is('nilai/nilai/mahasiswa/choose-mata-kuliah') ||
                                    request()->is('mahasiswa/*/mata-kuliah/*') ||
                                    request()->is('nilai/mahasiswa/*') ||
                                    request()->is('get-kelas-by-periode')
                                        ? 'active'
                                        : '' }}">
                                    <i class="fas fa-edit nav-icon"></i>
                                    <p>Nilai Mahasiswa</p>
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

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('penilaian.cpl.choose_mahasiswa') }}"
                                    class="nav-link {{ request()->is('penilaian/cpl/*') ? 'active' : '' }}">
                                    <i class="fas fa-chart-line nav-icon"></i>
                                    <p>Penilaian CPL</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
