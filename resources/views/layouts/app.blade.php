<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard SDA')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="vendor/fontawesome-free-6.5.1-web/css/all.min.css">
    {{-- <link rel="stylesheet" href="fonts/HelveticaNeue/HelveticaNeue.css"> --}}
    <link rel="stylesheet" href="fonts/Poppins\Poppins.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="/img/logo-uwp1.png" type="image/x-icon">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            /* background-color: #f4f4f9; */
            overflow-x: hidden;
            overflow-y: visible;
            /* iki gawe scrol sing horizontal e catatt */
        }

        /* Dropdown Button */
        .dropdown-toggle {
            background-color: #ffffff;
            /* Same color as sidebar */
            color: #ffffff;
            /* White text color */
            border: none;
            /* Remove default border */
            font-weight: 450;
            /* Adjust font weight to match sidebar links */
            transition: background-color 0.3s ease;
        }

        .dropdown-toggle:hover {
            background-color: #34495E;
            /* Hover effect with a slightly darker red */
        }

        /* Dropdown Menu */
        .dropdown-menu {
            background-color: #2C3E50;
            /* Same color as sidebar */
            border-radius: 8px;
            /* Match sidebar rounded corners */
            border: 1px solid #DEB887;
            /* Same border style as sidebar */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Optional shadow for better visibility */
        }

        /* Dropdown Item */
        .dropdown-item {
            background-color: #2C3E50;
            /* Same color as sidebar */
            color: #ffffff;
            /* White text color */
            padding: 10px 15px;
            border-radius: 8px;
            /* Match sidebar rounded corners */
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        .dropdown-item:hover {
            background-color: #2C3E50;
            /* Hover effect with a slightly darker red */
            transform: scale(1.05);
            /* Slightly increase size on hover */
        }

        /* Ensure the icons in dropdown match the sidebar */
        .dropdown-item i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .dropdown-item:hover i {
            color: #D3D3D3;
            /* Hover effect for icons */
        }


        .sidebar {
            background-color: #2C3E50;
            color: #ffffff;
            height: 100vh;
            padding: 20px;
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            /* Ukuran e width */
            z-index: 1000;
            /* border-right: 2px solid #ffffff; */
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar a {
            color: #ffffff;
            text-decoration: none;
            padding: 10px 15px;
            /* padding gawe jarak */
            display: flex;
            align-items: center;
            border-radius: 8px;
            /* gawe coner sing border ben apik */
            margin-bottom: 10px;
            /* Decreased margin */
            transition: background-color 0.3s, transform 0.2s;
            font-weight: 450;
            font-size: 1.0rem;
            /* deleh font size */
        }

        .sidebar a:hover {
            background-color: #34495E;
            transform: scale(1.05);
            /* Slightly ukuran e hover */
        }

        .sidebar i {
            font-size: 1.5rem;
            /* kene tak deleh icon size */
            margin-right: 10px;
            /* Aspace gawe jarak tect eben masuk */
            transition: color 0.3s;
            /* Smooth color transition */
        }

        .sidebar a:hover i {
            color: #D3D3D3;
            /* hoper gawe effect nek arep di pejet */
        }

        .navbar-toggler {
            border: none;
        }

        /* Styles for main content */
        .main-content {
            padding: 20px;
            transition: margin-left 0.3s ease;
            /* padding: 20px; */
            background-color: #ffffff;
            min-height: 100vh;
            margin-left: 0;
            /* buat hilangkan jarak navbar dengann sidebar */
            padding-left: 0;
            /* buat hilangkan jarak navbar dengann sidebar */
        }

        .navbar-custom {
            position: sticky;
            display: flex;
            height: auto;
            width: auto;
            /* padding: 48px 20px; */
            align-items: center;
            justify-content: center;
            padding-bottom: 10px;
            padding-top: 10px;
            background: url('{{ asset('img/pat_04.png') }}') #004680 !important;
            /* background: url('{{ asset('img/pat_04.png') }}') #0052A2 !important; */
            top: 0px;
            z-index: 1030;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand-logo img {
            width: 70px;
            margin-right: 0px;
            align-items: center;
        }

        .logo-wrapper {
            background-color: white;
            border-radius: 30px;
            /* Membuat sudut melengkung */
            padding: 15px;
            /* Memberikan ruang di sekitar logo */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Menambahkan bayangan */
            display: flex;
            align-items: center;
            justify-content: center;
            width: 90px;
            /* Ukuran kotak logo */
            height: 90px;
            /* Ukuran kotak logo */
            margin-right: 15px;
            /* Jarak antara logo dan teks */
        }

        /* .navbar-brand-text {
            line-height: 1.2;
        } */



        .user-menu {
            cursor: pointer;
        }

        .breadcrumb-container {
            position: sticky;
            background-color: white;
            padding: 10px 20px;
            padding-left: 2%;
            top: 143px;
            /* Offset dari navbar */
            z-index: 1020;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            /* padding: 10px 10px; */
            font-size: 12px;
        }

        .btn-primary {
            background-color: #0052A2;
            border: none;
            font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #003580;
        }

        .list-group-item {
            background-color: white;
            border: none;
            padding: 10px 15px;
        }

        .list-group-item a {
            text-decoration: none;
            color: #000000;
        }

        .list-group-item a:hover {
            color: #ffffff;
            background-color: #0052A2
        }

        /* Menambahkan jarak antar breadcrumb */
        .breadcrumb-item {
            margin-right: 15px;
        }

        /* Menetapkan warna tombol dropdown menjadi putih */
        .breadcrumb-item .dropdown-toggle {
            background-color: white;
            border: none;
            color: black;
            text-decoration: none;
        }

        /* Menampilkan dropdown saat hover tanpa perlu klik */
        .breadcrumb-item.dropdown:hover .dropdown-menu {
            display: block;

        }

        /* Menghilangkan background dan border dropdown menu */
        .dropdown-menu {
            /* background-color: white; */
            border: none;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            /* Tambahkan shadow */
            background-color: white;
        }

        /* Menambahkan jarak antara item dropdown */
        .dropdown-item {
            padding: 10px 20px;
            background-color: white;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: '';
            /* Menghilangkan pemisah default */
        }

        .breadcrumb-container .breadcrumb-item a {
            color: black;
            /* Warna teks hitam */
            text-decoration: none;
            /* Menghilangkan garis bawah */
            font-family: "Poppins", sans-serif;
            font-weight: 300;
            font-size: 15px;
            /* Font tipis */
            padding: 5px 10px;
            /* Ruang di sekitar teks */
            border: none;
            /* Tidak ada border secara default */
            border-radius: 4px;
            /* Sudut melengkung */
            transition: all 0.3s ease;
            /* Transisi halus */
        }

        .breadcrumb-container .breadcrumb-item a:hover {
            background-color: #0052A2;
            /* Latar belakang biru saat di-hover */
            color: white;
            /* Warna teks putih saat di-hover */
            border: 1px solid #0052A2;
            /* Border biru */
            text-decoration: none;
            /* Tetap tanpa garis bawah */
        }

        /* Styling untuk Dropdown Profil */
        .navbar-custom .dropdown {
            position: absolute;
            top: 50%;
            right: 100px;
            /* Atur jarak dari sisi kanan */
            transform: translateY(-50%);
        }

        .navbar-custom .dropdown-toggle {
            display: flex;
            align-items: center;
            background: url('{{ asset('img/pat_04.png') }}') #0052A2 !important;
            border-radius: 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .navbar-custom .dropdown-toggle:hover {
            background-color: rgba(255, 0, 0, 0.2);
            /* Hitam transparan tipis */
            color: white;
        }

        .navbar-custom .dropdown-toggle img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }

        .navbar-custom .dropdown-menu {
            width: 250px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .navbar-custom .dropdown-menu .dropdown-header {
            background-color: #004680;
            color: white;
            text-align: center;
            padding: 15px;
        }

        .navbar-custom .dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .navbar-custom .dropdown-menu .dropdown-item i {
            font-size: 18px;
            margin-right: 10px;
        }

        .navbar-custom .dropdown-menu .dropdown-item:hover {
            background-color: #f5f5f5;
        }

        .navbar-custom .dropdown:hover .dropdown-menu {
            display: block;
        }

        .navbar-custom .dropdown-toggle i {
            font-size: 30px;
            margin-right: 10px;
        }

        .navbar-custom .dropdown-menu {
            width: 250px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .navbar-custom .dropdown-menu .dropdown-header {
            background-color: #004680;
            color: white;
            text-align: center;
            padding: 15px;
        }

        .navbar-custom .dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            font-size: 14px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .navbar-custom .dropdown-menu .dropdown-item i {
            font-size: 18px;
            margin-right: 10px;
        }

        .navbar-custom .dropdown-menu .dropdown-item:hover {
            background-color: rgba(0, 0, 0, 0.1);
            /* Hitam transparan lebih terang */
            color: #0052A2;
        }

        .profile-header {
            background: url(../img/pat_04.png) #0052A2;
            color: white;
            text-align: center;
            padding: 20px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .profile-header .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: gray;
            color: white;
            font-size: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }

        .profile-actions-container {
            background-color: #ffffff;
            padding: 15px 20px;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            display: flex;
            justify-content: space-between;
            /* Untuk meratakan dengan jarak otomatis */
            gap: 20px;
            /* Atur jarak antar tombol, bisa disesuaikan */
        }

        .profile-menu-btn {
            border: 1px solid #ddd;
            background-color: #ffffff;
            color: #333;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 6px;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .profile-menu-btn i {
            margin-right: 5px;
            font-size: 16px;
        }

        .profile-menu-btn:hover {
            background-color: #f8f9fa;
            color: #0052A2;
            border-color: #bbb;
        }


        /* atur responsive di media query biasanya untuk laptop atau desktop */
        /* Aturan CSS akan berlaku ketika lebar layar lebih besar dari atau sama dengan nilai yang ditentukan. */
        /* lebar min */
        @media (min-width: 992px) {
            .navbar-custom {
                display: flex;
                justify-content: space-between;
            }

            .breadcrumb-container {
                position: sticky;
                background-color: white;
                padding: 10px 20px;
                /* padding-left: 2%; */
                top: 0px;
                /* Offset dari navbar */
                /* z-index: 1020; */
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                /* padding: 10px 10px; */
                font-size: 12px;
            }

            .navbar-brand-logo {
                display: flex;
                align-items: center;
            }

            .logo-wrapper {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 90px;
                /* Adjust logo wrapper size */
                height: 90px;
            }

            .sidebar {
                transform: translateX(0);
                position: relative;
                height: auto;
            }

            .navbar-brand-text {
                display: flex;
                flex-direction: column;
                /* justify-content: center; */
                padding-left: 15px;
            }

            .navbar-brand-text .main-text {
                font-weight: bold;
                font-size: 1.8rem;
                line-height: 1.2;

            }

            .navbar-brand-text .sub-text {
                font-size: 1.5rem;
                color: #ffffff;

            }

            .navbar-brand-logo {
                display: flex;
                /* margin-top: 10px; */
                /* padding-top: 20%; */
                /* align-items: center;
                text-align: center; */
            }



            .main-content {
                margin-left: 220px;
            }

            .navbar-toggler {
                display: none;
            }
        }


        @media (min-width: 1025px) {
            /* Untuk desktop besar */

        }


        /* atur responsive di media query biasanya untuk table/ponsel atau layar kecil*/
        /* Aturan CSS akan berlaku ketika lebar layar kurang dari atau sama dengan nilai yang ditentukan. */
        /* lebar max */
        @media (max-width: 576px) {
            .navbar-custom {
                flex-direction: column;
                padding: 10px;
                height: auto;
                /* Pastikan tinggi navbar menyesuaikan */
            }





            .dropdown-menu {
                width: 100%;
                box-shadow: none;
                /* Hilangkan shadow untuk perangkat kecil */
            }

            .sidebar {
                width: 100%;
                height: auto;
            }

            .content {
                padding: 15px;
                /* Kurangi padding pada konten */
            }
        }
    </style>
</head>

<body>
    <!-- Wrapper -->
    <div class="wrapper" style="background-color: #0080ff;">
        <!-- Tambahkan background image pada wrapper -->
        <div class="background"
            style="background-image: url('/img/texture.jpg'); background-repeat: repeat; min-height: 100vh;">
            <!-- Sticky Navbar -->
            <nav class="navbar-custom navbar-expand-lg" style="background-color: #004680">
                <div class="container-fluid d-flex align-items-center">

                    <!-- Menu Hamburger -->
                    <button class="btn btn-primary d-lg-none me-3" type="button" data-bs-toggle="collapse"
                        data-bs-target="#breadcrumbMenu" aria-expanded="false" aria-controls="breadcrumbMenu">
                        <i class="bi bi-list"></i>
                    </button>

                    <!-- Logo -->
                    <div class="navbar-brand-logo d-flex align-items-center">
                        <div class="logo-wrapper">
                            <img src="/img/logo-uwp1.png" alt="Logo Universitas Wijaya Putra">
                        </div>
                        <div class="navbar-brand-text">
                            <div class="main-text" style="color: white;">Outcome Based Education</div>
                            <div class="sub-text" style="color: white;">Universitas Wijaya Putra</div>
                        </div>
                    </div>

                    <!-- Dropdown Profil -->
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="profileDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <span>Pak Alven</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown"
                            style="width: 250px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                            <div class="profile-header">
                                <div class="profile-img">MR</div>
                                <strong>Pak Alven</strong>
                                <p class="mb-0">Kps</p>
                                <p class="mb-0">Teknik Informatika</p>
                            </div>
                            <div class="profile-actions-container">
                                <a href="#" class="btn profile-menu-btn">
                                    <i class="bi bi-person"></i> Profil
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                                <a href="#" class="btn profile-menu-btn"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </a>
                            </div>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Sticky Breadcrumb -->
            <div class="breadcrumb-container">
                <ol class="breadcrumb mb-0 d-none d-lg-flex">
                    <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                    <li class="breadcrumb-item dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">CPL</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/CPL/index">Daftar CPL</a></li>
                            <li><a class="dropdown-item" href="/CPL/create">Tambah CPL</a></li>
                        </ul>
                    </li>
                    <li class="breadcrumb-item dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">PL</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/PL/index">Daftar PL</a></li>
                            <li><a class="dropdown-item" href="/PL/create">Tambah PL</a></li>
                        </ul>
                    </li>
                    <li class="breadcrumb-item dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">CPMK</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/CPMK/index">Daftar CPMK</a></li>
                            <li><a class="dropdown-item" href="/CPMK/create">Tambah CPMK</a></li>
                        </ul>
                    </li>
                    <li class="breadcrumb-item dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">BK</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/BK/index">Daftar BK</a></li>
                            <li><a class="dropdown-item" href="/BK/create">Tambah BK</a></li>
                        </ul>
                    </li>
                    <li class="breadcrumb-item dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">Pemetaan</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('pemetaan_CPL-PL.index') }}">Pemetaan
                                    CPL-PL</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('pemetaan_CPMK-PL.index') }}">Pemetaan
                                    CPMK-PL</a>
                            </li>
                        </ul>
                    </li>
                </ol>

                <div class="collapse d-lg-none" id="breadcrumbMenu">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <a href="#" class="btn btn-primary w-100">Beranda</a>
                        </li>
                        <li class="list-group-item">
                            <a href="#" class="dropdown-toggle btn btn-primary w-100"
                                data-bs-toggle="dropdown">CPL</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/CPL/index">Daftar CPL</a></li>
                                <li><a class="dropdown-item" href="/CPL/create">Tambah CPL</a></li>
                            </ul>
                        </li>
                        <li class="list-group-item">
                            <a href="#" class="dropdown-toggle btn btn-primary w-100"
                                data-bs-toggle="dropdown">PL</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/PL/index">Daftar PL</a></li>
                                <li><a class="dropdown-item" href="/PL/create">Tambah PL</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Content Section -->
            <div class="content">
                @yield('content')
            </div>
            <!-- End Content Section -->
        </div>
    </div>
    <!-- End Wrapper -->
    <!-- Bootstrap 5 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
