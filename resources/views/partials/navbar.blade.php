<nav class="main-header navbar navbar-expand navbar-white navbar-light"
    style="height: 76px; background: url('{{ asset('img/pat_04.png') }}') #004680 !important;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item hamburger-menu">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
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

@section('css')
<style>
    /* Pastikan elemen hamburger menu terlihat secara default */
    .hamburger-menu {
        display: block;
    }

    /* Sembunyikan hamburger menu pada layar besar (>= 768px) */
    @media (min-width: 768px) {
        .navbar-nav .nav-item.hamburger-menu {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 250px !important; /* Sesuaikan dengan lebar sidebar */
            transition: margin-left 0.3s ease-in-out;
        }
    }

    /* Pastikan hamburger menu terlihat dan sidebar collapsed pada layar kecil (< 768px) */
    @media (max-width: 767.98px) {
        .navbar-nav .nav-item.hamburger-menu {
            display: block !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
        .sidebar-mini.sidebar-collapse .main-sidebar {
            transform: translateX(-100%);
        }
        .sidebar-mini.sidebar-collapse .content-wrapper {
            margin-left: 0 !important;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Pastikan jQuery dimuat
        if (typeof jQuery === 'undefined') {
            console.error('jQuery tidak dimuat!');
        } else {
            // Atur status sidebar berdasarkan lebar layar
            function adjustSidebar() {
                if ($(window).width() >= 768) {
                    $('body').removeClass('sidebar-collapse'); // Sidebar terbuka pada layar besar
                } else {
                    $('body').addClass('sidebar-collapse'); // Sidebar collapsed pada layar kecil
                }
            }

            // Jalankan saat halaman dimuat
            adjustSidebar();

            // Jalankan saat ukuran layar berubah
            $(window).resize(function() {
                adjustSidebar();
            });

            // Toggle sidebar saat hamburger menu diklik
            $('[data-widget="pushmenu"]').on('click', function() {
                $('body').toggleClass('sidebar-collapse');
            });
        }
    });
</script>
@endsection