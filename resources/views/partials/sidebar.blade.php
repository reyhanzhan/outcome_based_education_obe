<aside class="main-sidebar sidebar-light elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link" style="background: url('{{ asset('img/pat_04.png') }}') #004680 !important;">
        <img src="{{ asset('img/logo_obe_crop.png') }}" alt="Logo" class="img-fluid"
            style="width: 20rem; height: auto;">
    </a>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <nav class="mt-0">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Manajemen Data -->
                @if (Auth::check() && Auth::user()->role === 'kps')
                    <li
                        class="nav-item has-treeview {{ in_array(strtolower(request()->segment(1)), ['bk', 'mk', 'cpmk', 'subcpmk', 'pl', 'cpl']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(strtolower(request()->segment(1)), ['bk', 'mk', 'cpmk', 'subcpmk', 'pl', 'cpl']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-folder"></i>
                            <p>Manajemen Data</p>
                            <i class="right fas fa-angle-left"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('pl.index', ['kode_prodi' => Auth::user()->kode_prodi]) }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'pl' ? 'active' : '' }}">
                                    <p>Profil Lulusan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cpl.list') }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpl' ? 'active' : '' }}">
                                    <p>Capaian Profil Lulusan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('bk.index') }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'bk' ? 'active' : '' }}">
                                    <p>Bahan Kajian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('cpmk.index') }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpmk' ? 'active' : '' }}">
                                    <p>Capaian Pembelajaran Mata Kuliah</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('mk.index') }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'mk' ? 'active' : '' }}">
                                    <p>Mata Kuliah</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Pengelolaan Dosen -->
                @if (Auth::check() && Auth::user()->role === 'kps')
                    <li class="nav-item has-treeview {{ request()->is('dosen*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('dosen*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tie"></i>
                            <p>Dosen</p>
                            <i class="right fas fa-angle-left"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('dosen.index') }}"
                                    class="nav-link {{ request()->routeIs('dosen.index') ? 'active' : '' }}">
                                    <p>Daftar Dosen</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('dosen.create') }}"
                                    class="nav-link {{ request()->routeIs('dosen.create') ? 'active' : '' }}">
                                    <p>Tambah Dosen</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Pengelolaan Mahasiswa -->
                @if (Auth::check() && Auth::user()->role === 'kps')
                    <li
                        class="nav-item has-treeview {{ request()->is('mahasiswa*') && !request()->is('nilai/mahasiswa*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->is('mahasiswa*') && !request()->is('nilai/mahasiswa*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Mahasiswa</p>
                            <i class="right fas fa-angle-left"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('mahasiswa.index') }}"
                                    class="nav-link {{ request()->routeIs('mahasiswa.index') ? 'active' : '' }}">
                                    <p>Daftar Mahasiswa</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('mahasiswa.create') }}"
                                    class="nav-link {{ request()->routeIs('mahasiswa.create') ? 'active' : '' }}">
                                    <p>Tambah Mahasiswa</p>
                                </a>
                            </li>
                            
                        </ul>
                    </li>
                @endif

                <!-- Kurikulum & Kelas -->
                @if (Auth::check() && Auth::user()->role === 'kps')
                    <li
                        class="nav-item has-treeview {{ request()->is('kurikulum*') || request()->is('kelas*') || request()->is('krs*') ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ request()->is('kurikulum*') || request()->is('kelas*') || request()->is('krs*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Kurikulum & Kelas</p>
                            <i class="right fas fa-angle-left"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('kurikulum.index') }}"
                                    class="nav-link {{ request()->is('kurikulum*') ? 'active' : '' }}">
                                    <p>Kurikulum</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('kelas.index', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ request()->is('kelas*') ? 'active' : '' }}">
                                    <p>Kelas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('krs.index', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ request()->is('krs*') ? 'active' : '' }}">
                                    <p>KRS</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Pemetaan -->
                @if (Auth::check() && Auth::user()->role === 'kps')
                    <li
                        class="nav-item has-treeview {{ in_array(strtolower(request()->segment(1)), ['cpl-pl', 'cpl-mk', 'cpl-bk', 'cpmk-cpl', 'cpmk-mk', 'cpl-cpmk-mk']) ? 'menu-open' : '' }}">
                        <a href="#"
                            class="nav-link {{ in_array(strtolower(request()->segment(1)), ['cpl-pl', 'cpl-mk', 'cpl-bk', 'cpmk-cpl', 'cpmk-mk', 'cpl-cpmk-mk']) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sitemap"></i>
                            <p>Pemetaan</p>
                            <i class="right fas fa-angle-left"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('Cpl_Pl.index', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpl-pl' ? 'active' : '' }}">
                                    <p>PL - CPL</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('Cpl_Bk.index', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpl-bk' ? 'active' : '' }}">
                                    <p>CPL - BK</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('Cpmk_Cpl.index', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpmk-cpl' ? 'active' : '' }}">
                                    <p>CPL - CPMK</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('Cpmk_Mk.index', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpmk-mk' ? 'active' : '' }}">
                                    <p>CPMK - MK</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('Cpl_Mk.index', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpl-mk' ? 'active' : '' }}">
                                    <p>MK - CPL</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('Cpmk_Cpl_Mk.index', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ strtolower(request()->segment(1)) == 'cpl-cpmk-mk' ? 'active' : '' }}">
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
                            <p>Penilaian</p>
                            <i class="right fas fa-angle-left"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('pembobotan.index', ['kode_prodi' => Auth::user()->kode_prodi, 'tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ request()->is('pembobotan*') ? 'active' : '' }}">
                                    <p>Pembobotan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah', ['tahun' => session('selected_year', '')]) }}"
                                    class="nav-link {{ request()->is('nilai/mahasiswa*') || request()->is('penilaian/cpl/*') || request()->is('penilaian/cpmk/*') || request()->routeIs('nilai.mahasiswa.choose_mata_kuliah') || request()->is('get-kelas-by-periode') ? 'active' : '' }}">
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

<style>
    /* Import Google Font */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .sidebar-light .nav-sidebar .nav-link {
        font-family: 'Poppins', sans-serif !important;
        font-weight: 500 !important;
        color: #222 !important;
        transition: all 0.3s ease-in-out;
    }

    /* Modern Sidebar Styling */
    .main-sidebar {
        /* width: 250px !important;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08) !important;
        border-right: 1px solid #e2e8f0 !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        -ms-overflow-style: none !important; */
        scrollbar-width: none !important;
        overflow-x: hidden !important;
    }

    .main-sidebar::-webkit-scrollbar {
        display: none !important;
    }

    /* Sidebar Collapse Mode */
    .sidebar-collapse .main-sidebar {
        /* width: 4.6rem !important;  */
        /* transition: width 0.3s ease, box-shadow 0.3s ease !important;
        z-index: 1031 !important; */
    }

    /* .sidebar-collapse .nav-link p {
        display: none !important;
        opacity: 0;
        transition: opacity 0.2s ease;
        z-index: 1031 !important;
    } */

    /* .sidebar-collapse .nav-item.has-treeview .nav-treeview {
        display: none !important;
        position: absolute;
        top: 0;
        left: 100%;
        width: 250px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0 8px 8px 0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 0.5rem 0;
        z-index: 1031 !important;
    } */

    /* Submenu Aktif Saat Diklik */
    .sidebar-collapse .nav-item.has-treeview.active>.nav-treeview {
        display: block !important;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Brand Section */
    .brand-link {
        background: linear-gradient(135deg, #004680 0%, #0066cc 100%) !important;
        border-bottom: 1px solid #00357a !important;
        position: relative !important;
        overflow: hidden !important;
        padding: 1rem !important;
        text-align: center !important;
        transition: padding 0.3s ease;
    }

    .sidebar-collapse .brand-link {
        padding: 0.5rem !important;
    }

    .brand-link::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        transform: rotate(45deg);
        transition: opacity 0.3s ease;
        opacity: 0;
    }

    .brand-link:hover::before {
        /* opacity: 1; */
        max-width: 100% !important;
        height: auto !important;
        display: block !important;
        margin: 0 auto !important;
        filter: none !important;
        transition: transform 0.3s ease !important;

    }

    .brand-link img {
        max-width: 100% !important;
        height: auto !important;
        display: block !important;
        margin: 0 auto !important;
        filter: none !important;
        transition: transform 0.3s ease !important;
    }

    .brand-link:hover img {
        transform: scale(1.05) !important;
    }

    .sidebar-collapse .brand-link img {
        /* max-width: 118rem !important; */
        /* width: 50px !important;
        height: 50px !important; */
        height: auto !important;
        width: auto !important;
        /* max-height: 80px !important; */


        height: auto !important;
        display: block !important;
        margin: 0 auto !important;
        filter: none !important;
        transition: transform 0.3s ease !important;


    }

    /* Sidebar Content */
    .sidebar {
        /* height: calc(100vh - 80px) !important;
        padding: 1rem 0 !important;
        -ms-overflow-style: none !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        position: relative !important; */
        scrollbar-width: none !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }


    .sidebar::-webkit-scrollbar {
        display: none !important;
    }

    /* .sidebar::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 20px;
        background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.1));
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    } */

    /* .sidebar:hover::after {
        opacity: 1;
    } */

    /* Navigation Items */
    .nav-sidebar .nav-item {
        margin: 0.5rem 0.75rem !important;
        position: relative !important;
    }

    /* Main Navigation Links */
    .sidebar-light .nav-sidebar .nav-link {
        /* font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        color: #1e293b !important;
        padding: 0.75rem 1rem !important;
        border-radius: 8px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 0.125rem !important;
        display: flex !important;
        align-items: center !important; */
        overflow: hidden !important;
    }

    .sidebar-light .nav-sidebar .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, #004680, #0066cc);
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: -1;
    }

    .sidebar-collapse .nav-sidebar .nav-link {
        padding: 0.75rem !important;
        justify-content: center !important;
    }

    /* Icon Styling */
    .sidebar-light .nav-sidebar .nav-link i {
        color: #1e293b !important;
        width: 20px !important;
        margin-right: 0 rem !important;
        font-size: 1.2rem !important;
        transition: color 0.3s ease !important;
        text-align: center !important;
    }

    .sidebar-collapse .nav-sidebar .nav-link i {
        margin-right: 0 !important;
    }

    /* Hover Effects */
    .sidebar-light .nav-sidebar .nav-link:hover {
        color: white !important;
        background: linear-gradient(135deg, #004680 0%, #0066cc 100%) !important;
        box-shadow: none !important;
    }

    .sidebar-light .nav-sidebar .nav-link:hover::before {
        width: 100% !important;
    }

    .sidebar-light .nav-sidebar .nav-link:hover i {
        color: white !important;
    }

    /* Active State */
    .sidebar-light .nav-sidebar .nav-link.active {
        color: white !important;
        background: linear-gradient(135deg, #004680 0%, #0066cc 100%) !important;
        font-weight: 600 !important;
        position: relative !important;
    }

    .sidebar-light .nav-sidebar .nav-link.active i {
        color: white !important;
    }

    /* Dropdown Arrow */
    .nav-item.has-treeview>.nav-link::after {
        font-family: 'Font Awesome 6 Free' !important;
        font-weight: 900 !important;
        position: absolute !important;
        right: 1rem !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), color 0.3s ease !important;
        color: #1e293b !important;
    }

    .nav-item.has-treeview.menu-open>.nav-link::after {
        transform: translateY(-50%) rotate(90deg) !important;
    }

    .sidebar-light .nav-sidebar .nav-link:hover::after,
    .sidebar-light .nav-sidebar .nav-link.active::after {
        color: white !important;
    }

    /* Remove default AdminLTE arrow styling */
    .nav-item.has-treeview>.nav-link::before {
        display: none !important;
    }

    /* Submenu Styling */
    .nav-treeview {
        max-height: 0 !important;
        overflow: hidden !important;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease !important;
        opacity: 0 !important;
        padding: 0 !important;
        margin: 0.5rem 0 0 0 !important;
    }

    .nav-item.has-treeview.menu-open .nav-treeview {
        max-height: 600px !important;
        opacity: 1 !important;
        display: block !important;
    }

    .nav-treeview .nav-item {
        margin: 0.25rem 0.5rem !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link {
        padding: 0.5rem 1rem 0.5rem 2rem !important;
        font-size: 0.85rem !important;
        color: #334155 !important;
        border-radius: 6px !important;
        position: relative !important;
        font-weight: 500 !important;
        margin-bottom: 0.125rem !important;
        margin-left: 0.75rem !important;
        margin-right: 0.5rem !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link p {
        margin-left: 0.25rem !important;
        transition: color 0.3s ease !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link::before {
        content: '';
        position: absolute;
        left: 1.25rem !important;
        top: 50%;
        transform: translateY(-50%);
        width: 4px !important;
        height: 12px !important;
        background: #94a3b8 !important;
        border-radius: 2px !important;
        transition: all 0.3s ease !important;
        z-index: 2 !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link:hover {
        color: white !important;
        position: relative !important;
        z-index: 2 !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link:hover::before {
        background: white !important;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3) !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link:hover::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: calc(100% - 1rem) !important;
        height: 100%;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        border-radius: 6px !important;
        z-index: -1 !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link.active {
        color: white !important;
        position: relative !important;
        z-index: 2 !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link.active::before {
        background: white !important;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3) !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link.active::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: calc(100% - 1rem) !important;
        height: 100%;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        border-radius: 6px !important;
        z-index: -1 !important;
    }

    .sidebar-light .nav-sidebar .nav-treeview .nav-link:hover,
    .sidebar-light .nav-sidebar .nav-treeview .nav-link.active {
        box-shadow: none !important;
    }

    /* Connection Line for Tree Structure */
    .nav-item.has-treeview {
        position: relative !important;
    }

    .nav-item.has-treeview.menu-open::before {
        content: '';
        position: absolute;
        left: 1.5rem !important;
        top: 3.5rem;
        width: 1px;
        height: calc(100% - 3.5rem);
        background: linear-gradient(to bottom, #e2e8f0, transparent) !important;
        z-index: 1 !important;
        max-height: calc(100% - 4rem) !important;
    }


    .nav-sidebar .nav-item,
    .nav-sidebar .nav-link,
    .nav-sidebar .nav-treeview {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    /* Focus States for Accessibility */
    .sidebar-light .nav-sidebar .nav-link:focus {
        outline: 2px solid #007bff !important;
        outline-offset: 2px !important;
    }

    /* Loading Animation */
    .nav-link.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: translateY(-50%) rotate(0deg);
        }

        100% {
            transform: translateY(-50%) rotate(360deg);
        }
    }

    /* Tooltip for truncated text */
    .nav-link[title]:hover::after {
        content: attr(title);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: #1e293b;
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 1000;
        margin-left: 0.5rem;
        opacity: 0;
        animation: fadeIn 0.3s ease-in-out forwards;
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }

    /* Badge/Counter Support */
    .nav-link .badge {
        position: absolute !important;
        top: 0.5rem !important;
        right: 2.5rem !important;
        background: #ef4444 !important;
        color: white !important;
        font-size: 0.625rem !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        min-width: 18px !important;
        text-align: center !important;
    }

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        .main-sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
            border-right: 1px solid #334155 !important;
        }

        .sidebar-light .nav-sidebar .nav-link {
            color: #cbd5e1 !important;
        }

        .sidebar-light .nav-sidebar .nav-link i {
            color: #cbd5e1 !important;
        }

        .sidebar-light .nav-sidebar .nav-treeview .nav-link {
            color: #94a3b8 !important;
        }

        .nav-item.has-treeview.menu-open::before {
            background: linear-gradient(to bottom, #334155, transparent) !important;
        }
    }

    /* Print Styles */
    @media print {
        .main-sidebar {
            display: none !important;
        }
    }

    /* High Contrast Mode */
    @media (prefers-contrast: high) {
        .sidebar-light .nav-sidebar .nav-link {
            border: 1px solid #475569 !important;
        }

        .sidebar-light .nav-sidebar .nav-link.active {
            border: 2px solid #ffffff !important;
        }
    }

    /* Reduced Motion */
    @media (prefers-reduced-motion: reduce) {

        .sidebar-light .nav-sidebar .nav-link,
        .nav-treeview,
        .nav-item {
            transition: none !important;
            animation: none !important;
        }
    }
</style>
