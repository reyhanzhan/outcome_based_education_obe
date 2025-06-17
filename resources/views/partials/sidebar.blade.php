<style>
    /* Gaya dasar untuk semua nav-link di sidebar */
    .sidebar-light .nav-sidebar .nav-link {
        font-family: 'Poppins', sans-serif !important;
        font-weight: 500 !important;
        color: #222 !important;
        transition: all 0.3s ease-in-out;
    }

    /* Ikon default */
    .sidebar-light .nav-sidebar .nav-link i {
        color: #222 !important;
    }

    /* Warna menu saat aktif (parent dan child) */
    .sidebar-light .nav-sidebar .nav-link.active {
        color: white !important;
        background-color: #004680 !important;
        font-weight: 500 !important;
    }

    /* Ikon saat menu aktif */
    .sidebar-light .nav-sidebar .nav-link.active i {
        color: white !important;
    }

    /* Warna menu saat di-hover */
    .sidebar-light .nav-sidebar .nav-link:hover {
        color: white !important;
        background-color: #004680 !important;
    }

    /* Ikon saat hover */
    .sidebar-light .nav-sidebar .nav-link:hover i {
        color: white !important;
    }

    /* Gaya submenu */
    .sidebar-light .nav-sidebar .nav-treeview .nav-link {
        font-weight: 500 !important;
        color: #333 !important;
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
    .nav-sidebar .nav-item.has-treeview.menu-open>.nav-link {
        background-color: #dcdcdc !important;
        color: #222 !important;
        font-weight: bold;
    }

    /* Ikon parent menu saat menu terbuka */
    .nav-sidebar .nav-item.has-treeview.menu-open>.nav-link i {
        color: #222 !important;
    }

    /* Pastikan sidebar memiliki background putih */
    .sidebar-light {
        background-color: #ffffff !important;
    }

    /* Tambahkan scroll pada sidebar */
    .sidebar {
        height: calc(100px - 60px);
        /* Sesuaikan dengan tinggi header/brand-link */
        overflow-y: auto;
        /* Aktifkan scroll vertikal */
        padding-bottom: 20px;
        /* Berikan padding bawah agar konten tidak terpotong */
    }

    /* Pastikan dropdown menu tidak terpotong */
    .nav-treeview {
        position: relative;
        z-index: 1000;
        /* Pastikan dropdown di atas elemen lain */
    }

    /* Gaya sidebar di mode mobile */
    @media (max-width: 768px) {

        .main-sidebar,
        .sidebar {
            background-color: #ffffff !important;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            height: auto;
            /* Biarkan tinggi menyesuaikan konten di mobile */
            overflow-y: auto;
            /* Tetap aktifkan scroll di mobile */
        }
    }
</style>

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
                {{-- menu tambah dosen --}}
                <!-- Pengelolaan Dosen -->
                @if (Auth::check() && Auth::user()->role === 'kps')
                    <li class="nav-item has-treeview {{ request()->is('dosen*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('dosen*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>
                                Pengelolaan Dosen
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('dosen.create') }}"
                                    class="nav-link {{ request()->routeIs('dosen.create') ? 'active' : '' }}">
                                    <i class="fas fa-plus nav-icon"></i>
                                    <p>Tambah Dosen</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('dosen.index') }}"
                                    class="nav-link {{ request()->routeIs('dosen.index') ? 'active' : '' }}">
                                    <i class="fas fa-list nav-icon"></i>
                                    <p>Daftar Dosen</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- manajemen mhs --}}
                @if (Auth::check() && Auth::user()->role === 'kps')
                    <li
                        class="nav-item has-treeview {{ request()->is('mahasiswa*') && !request()->is('nilai/mahasiswa*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->is('mahasiswa*') && !request()->is('nilai/mahasiswa*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>
                                Pengelolaan Mahasiswa
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('mahasiswa.create') }}"
                                    class="nav-link {{ request()->routeIs('mahasiswa.create') ? 'active' : '' }}">
                                    <i class="fas fa-plus nav-icon"></i>
                                    <p>Tambah Mahasiswa</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('mahasiswa.index') }}"
                                    class="nav-link {{ request()->routeIs('mahasiswa.index') ? 'active' : '' }}">
                                    <i class="fas fa-list nav-icon"></i>
                                    <p>Daftar Mahasiswa</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Pengelolaan Kurikulum, KRS, dan Kelas (di bawah Pengelolaan Mahasiswa) --}}
                @if (Auth::check() && Auth::user()->role === 'kps')
                    <li
                        class="nav-item has-treeview {{ request()->is('kurikulum*') || request()->is('kelas*') || request()->is('krs*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->is('kurikulum*') || request()->is('kelas*') || request()->is('krs*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>
                                Pengelolaan Kurikulum & Kelas
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <!-- Kurikulum -->
                            <li class="nav-item">
                                <a href="{{ route('kurikulum.index') }}"
                                    class="nav-link {{ request()->is('kurikulum*') ? 'active' : '' }}">
                                    <i class="fas fa-book-open nav-icon"></i>
                                    <p>Kurikulum</p>
                                </a>
                            </li>
                            <!-- Kelas -->
                            <li class="nav-item">
                                <a href="{{ route('kelas.index') }}"
                                    class="nav-link {{ request()->is('kelas*') ? 'active' : '' }}">
                                    <i class="fas fa-chalkboard-teacher nav-icon"></i>
                                    <p>Kelas</p>
                                </a>
                            </li>
                            <!-- KRS -->
                            <li class="nav-item">
                                <a href="{{ route('krs.index') }}"
                                    class="nav-link {{ request()->is('krs*') ? 'active' : '' }}">
                                    <i class="fas fa-clipboard-list nav-icon"></i>
                                    <p>KRS</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


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
                                <a href="{{ route('pl.index', ['kode_prodi' => Auth::user()->kode_prodi]) }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'pl' ? 'active' : '' }}">
                                    <i class="fas fa-user-graduate nav-icon"></i>
                                    <p>Profil Lulusan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cpl.list') }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpl' ? 'active' : '' }}">
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
                        class="nav-item has-treeview {{ request()->is('pembobotan*') || request()->is('nilai*') || request()->is('penilaian*') || request()->is('visualisasi*') || request()->is('nilai/mahasiswa*') || request()->is('get-kelas-by-periode') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->is('pembobotan*') || request()->is('nilai*') || request()->is('penilaian*') || request()->is('visualisasi*') || request()->is('nilai/mahasiswa*') || request()->is('get-kelas-by-periode') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>
                                Penilaian
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('pembobotan.index', ['kode_prodi' => Auth::user()->kode_prodi]) }}"
                                    class="nav-link {{ request()->is('pembobotan*') ? 'active' : '' }}">
                                    <i class="fas fa-balance-scale nav-icon"></i>
                                    <p>Pembobotan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}"
                                    class="nav-link {{ request()->is('nilai/mahasiswa*') || request()->is('penilaian/cpmk/*') || request()->is('penilaian/cpl/*') || request()->is('get-kelas-by-periode') ? 'active' : '' }}">
                                    <i class="fas fa-edit nav-icon"></i>
                                    <p>Nilai Mahasiswa</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
