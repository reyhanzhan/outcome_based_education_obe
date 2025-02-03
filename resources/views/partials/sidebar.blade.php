<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="{{ asset('/img/logo-uwp1.png') }}" class="brand-image">
        <span class="brand-text font-weight-light" style="font-size:1rem">Outcome Based Education</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Alexander Pierce</a>
            </div>
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
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-folder"></i>
                        <p>
                            Manajemen Data
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('bk.index') }}" class="nav-link">
                                <i class="fas fa-book nav-icon"></i>
                                <p>Bahan Kajian</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('mk.index') }}" class="nav-link">
                                <i class="fas fa-book-open nav-icon"></i>
                                <p>Mata Kuliah</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('cpmk.index') }}" class="nav-link">
                                <i class="fas fa-tasks nav-icon"></i>
                                <p>Capaian Pembelajaran Mata Kuliah</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('pl.index') }}" class="nav-link">
                                <i class="fas fa-user-graduate nav-icon"></i>
                                <p>Profil Lulusan</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('cpl.index') }}" class="nav-link">
                                <i class="fas fa-file-alt nav-icon"></i>
                                {{-- <i class="far fa-circle nav-icon"></i> --}}
                                <p>Capaian Profil Lulusan</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Pemetaan start -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        {{-- <i class="nav-icon fas fa-map-marked-alt"></i> --}}
                        <i class="nav-icon fas fa-sitemap"></i>
                        <p>
                            Pemetaan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('Cpl_Pl.index') }}" class="nav-link">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPL - PL</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('Cpl_Mk.index') }}" class="nav-link">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPL - MK</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('Cpl_Bk.index') }}" class="nav-link">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPL - BK</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('Cpmk_Cpl.index') }}" class="nav-link">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPMK - CPL</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('Cpmk-Cpl-Mk.index') }}" class="nav-link">
                                <i class="fas fa-layer-group nav-icon"></i>
                                <p>CPMK - CPL - MK</p>
                            </a>
                        </li>
                    </ul>
                </li>
                {{-- pemetaan end --}}

                <li class="nav-item">
                    <a href="{{ route('logout') }}" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
