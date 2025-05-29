<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Outcome Based Education</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons CDN -->
    <link href="https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/logo-uwp1.png') }}" type="image/x-icon" />

    <style>
        * {
            outline: solid 1px transparent;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('{{ asset('img/pat_04.png') }}');
        }

        .login-container {
            display: flex;
            max-width: 900px;
            width: 100%;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            overflow: hidden;
        }

        /* Left Side - Welcome Section */
        .left-section {
            flex: 1;
            background: url('{{ asset('img/bg-login.jpg') }}') center/cover no-repeat;
            height: 100%;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem;
        }

        .left-section h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .left-section p {
            font-size: 1.5rem;
        }

        .social-icons {
            margin-top: 1.5rem;
        }

        .social-icons a {
            color: #fff;
            margin: 0 10px;
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: #ff4757;
        }

        /* Right Side - Login Form */
        .right-section {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem;
        }

        .right-section h2 {
            font-weight: 600;
            margin-bottom: 2rem;
            color: #333;
        }

        .form-control {
            border: none;
            border-bottom: 2px solid #ddd;
            border-radius: 0;
            margin-bottom: 1.5rem;
            box-shadow: none;
            padding-right: 2.5rem; /* Memberi ruang untuk ikon */
        }

        .form-control:focus {
            border-bottom: 2px solid #004680;
            outline: none;
            box-shadow: none;
        }

        /* Menyembunyikan ikon mata bawaan browser (khusus Edge) */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
            font-size: 1.2rem; /* Ukuran ikon yang konsisten */
            z-index: 1; /* Pastikan ikon di atas elemen lain */
        }

        .toggle-icon:hover {
            color: #004680;
        }

        .btn-primary {
            background-color: #004680;
            border: none;
            padding: 0.75rem 0;
            font-size: 1rem;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0d64ab;
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #555;
        }

        .right-section img {
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .left-section {
                display: none;
            }

            .right-section img {
                display: block;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>
    <!-- Login Container -->
    <div class="login-container">
        <!-- Left Section -->
        <div class="left-section" style="padding-bottom: 40%">
            <img src="{{ asset('img/logo_obe_crop.png') }}" alt="Logo" class="img-fluid mb-4"
                style="max-width: 300px; height: auto; background-color: rgba(0, 0, 0, 0.5); border-radius: 3px;">
        </div>

        <!-- Right Section -->
        <div class="right-section d-flex flex-column justify-content-center">
            <img src="{{ asset('img/logo-uwp1.png') }}" alt="Logo" class="img-fluid mb-4"
                style="max-width: 100px; height: auto; background-color: rgba(0, 0, 0, 0.5); border-radius: 3px;">
            <h2 class="text-center">Login</h2>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/login" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="nip"><i class="bx bx-id-card"></i> NIP</label><span style="color:red">*</span>
                    <input type="text" name="nip" id="nip" class="form-control"
                        placeholder="Masukkan NIP" value="{{ old('nip') }}" required>
                </div>

                <!-- Password Input -->
                <div class="mb-3 password-toggle">
                    <label for="password"><i class="bx bx-lock"></i> Password</label><span style="color:red">*</span>
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Masukkan password" required>
                    <i class="bx bx-hide toggle-icon" id="togglePassword"></i>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Toggle ikon antara bx-show dan bx-hide
            this.classList.toggle('bx-show');
            this.classList.toggle('bx-hide');
        });
    </script>
</body>

</html>