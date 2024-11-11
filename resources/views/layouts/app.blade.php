<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard SDA')</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="vendor/fontawesome-free-6.5.1-web/css/all.min.css">
    <link rel="stylesheet" href="fonts/HelveticaNeue/HelveticaNeue.css">
    <link rel="icon" href="/img/logo-uwp1.png" type="image/x-icon">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            overflow-x: hidden;

            /* iki gawe scrol sing horizontal e catatt */
        }

        /* Dropdown Button */
        .dropdown-toggle {
            background-color: #2C3E50;
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

        /* iki gawe responsif antar mobile karo pc */
        @media (min-width: 992px) {
            .sidebar {
                transform: translateX(0);
                position: relative;
                height: auto;
            }

            .main-content {
                margin-left: 220px;
                /* Adjust space for the sidebar */
            }

            .navbar-toggler {
                display: none;
                /* Hide the toggle button on larger screens */
            }
        }

        /* Styles for main content */
        .main-content {
            transition: margin-left 0.3s ease;
            padding: 20px;
            background-color: #ffffff;
            min-height: 100vh;
            /* Ensure it takes full height */
            margin-left: 0;
            /* Reset margin */
        }

        /* Menyembunyikan tombol close pada layar besar */
        @media (min-width: 992px) {
            .close-btn {
                display: none;
            }
        }



        @media (max-width: 991px) {
            .close-btn {
                display: block;
                margin-left: auto;
            }
        }
    </style>
</head>

<body>

    <!-- Wrapper gawe pembungkus-->
    <div class="wrapper" style="background-color: #2C3E50">
        <div class="row g-10">

            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <div class="d-flex align-items-center">
                    <a href="#" class="navbar-brand me-3">
                        <img src="{{ asset('img/logo-uwp.jpg') }}" alt="LOGO" width="130">
                    </a>
                    <hr class="text-white">
                    <button type="button" class="btn-close close-btn" id="closeSidebar"></button>
                    </style>
                </div>

                <!-- Sidebar Menu -->
                <div class="dropdown">
                    <hr class="text-white">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-mortarboard-fill"></i>
                        Profil Lulusan
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="/PL/index" class="dropdown-item">
                                <i class="bi bi-card-list"></i>
                                Daftar PL
                            </a>
                        </li>
                        <li>
                            <a href="/PL/create" class="dropdown-item" type="button">
                                <i class="bi bi-person-fill-add"></i>
                                Tambah PL
                            </a>
                        </li>
                    </ul>
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-trophy"></i>

                        Capaian Profil Lulusan
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="/CPL/index" class="dropdown-item" type="button">
                                <i class="bi bi-card-list"></i>
                                Daftar CPL
                            </a>
                        </li>
                        <li>
                            <a href="/CPL/create" class="dropdown-item" type="button">
                                <i class="bi bi-person-fill-add"></i>
                                Tambah CPL
                            </a>
                        </li>
                    </ul>

                    <hr class="text-white">

                    <!-- Hidden form for logout -->
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                    <!-- Styled as a link but triggers logout -->
                    <a href="" class="d-flex align-items-center"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </a>
                </div>


            </div>
            <!-- End Sidebar -->




            <!-- Main Content -->
            <div class="col-12 col-lg main-content" id="mainContent" style="background-image: url('/img/texture.jpg'); background-repeat:round;"
                style="overflow-x: hidden">
                <nav class="navbar navbar-expand-lg bg-body-secondary mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" id="toggleSidebar">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <a href="#" class="navbar-brand ms-auto d-none d-lg-inline">
                            <img src="{{ asset('img/logo-uwp.jpg') }}" alt="LOGO" width="50">
                        </a>
                    </div>
                </nav>
                <!-- Content Section -->

                @yield('content')
                
                {{-- end section --}}
            </div>
            <!-- End Main Content -->
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        });

        document.getElementById('closeSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.remove('show');
        });
    </script>
</body>

</html>
