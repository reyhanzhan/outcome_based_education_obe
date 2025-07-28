<nav class="main-header navbar navbar-expand-md navbar-white navbar-light"
    style="height: 80px; background: url('{{ asset('img/pat_04.png') }}') #004680 !important;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" id="userDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                <span class="ml-1 text-white">{{ Auth::user()->name ?? 'Guest' }}</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </li>
            </ul>

            <!-- Form Logout -->
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
    </ul>
</nav>

<style>
    /* Sembunyikan fas fa-bars pada layar >= 768px */
@media (min-width: 768px) {
    .main-header .navbar-nav .nav-item .nav-link .fas.fa-bars {
        display: none !important;
    }
}

/* Tampilkan fas fa-bars pada rentang 769px hingga 991px */
@media (min-width: 769px) and (max-width: 991px) {
    .main-header .navbar-nav .nav-item .nav-link .fas.fa-bars {
        display: inline-block !important; /* Atau display: block sesuai kebutuhan */
    }
}

/* Pastikan sidebar muncul 100% pada layar >= 991px */
@media (min-width: 991px) {
    .main-sidebar {
        transform: translateX(0) !important;
        width: 250px !important; /* Lebar maksimum sidebar */
        display: block !important;
    }
    .content-wrapper {
        margin-left: 250px !important; /* Sesuaikan dengan lebar sidebar */
    }
}


</style>
