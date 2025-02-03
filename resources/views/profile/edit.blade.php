@extends('layouts_baru.app')
@section('title', 'Tambah Capaian Pembelajaran Mata Kuliah')
@section('content')
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: linear-gradient(145deg, #ffffff, #f6f7f9);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .main-title {
            font-size: 2.25rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            background: linear-gradient(45deg, #2563eb, #1e40af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-picture-container {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            padding: 5px;
            background: linear-gradient(45deg, #3b82f6, #1e40af);
        }

        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            transition: all 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.02);
        }

        .btn-wrapper {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .btn-custom {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            min-width: 160px;
            border: none;
        }

        .btn-custom.btn-primary {
            background: linear-gradient(45deg, #3b82f6, #1e40af);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-custom.btn-danger {
            background: linear-gradient(45deg, #ef4444, #b91c1c);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-custom i {
            margin-right: 8px;
            font-size: 1.2rem;
        }

        .profile-instructions {
            text-align: center;
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            max-width: 80%;
            margin: 1.5rem auto;
            padding: 1rem;
            background: rgba(241, 245, 249, 0.8);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .alert {
            margin: 1rem 0;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        @media (max-width: 640px) {
            .form-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .btn-wrapper {
                flex-direction: column;
                align-items: center;
            }

            .btn-custom {
                width: 100%;
                max-width: 250px;
            }

            .profile-instructions {
                max-width: 100%;
            }
        }
    </style>

    <div class="container">
        <!-- Notifikasi sukses update profil start -->
        @if (session('success'))
            <div class="alert alert-success" id="success-notification">
                <i class="ion ion-checkmark-circle"></i> {{ session('success') }}
            </div>
            <script>
                setTimeout(function() {
                    document.getElementById('success-notification').style.display = 'none';
                }, 2000); // 2000ms = 2 detik
            </script>
        @endif
        {{-- Notifikasi sukses update profil end --}}

        <div class="form-container">
            <!-- Form untuk update foto -->
            <form id="form_update" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;"
                    onchange="this.form.submit();">
            </form>

            <!-- Form untuk hapus foto -->
            <form id="form_hapus" action="{{ route('profile.delete-photo') }}" method="POST">
                @csrf
                @method('DELETE')

                <h2 class="main-title">Foto Profil</h2>

                <div class="profile-picture-container">
                    <img src="{{ Auth::user()->profile_picture ? Storage::url(Auth::user()->profile_picture) : 'https://via.placeholder.com/180' }}"
                        alt="Profile Picture" class="profile-picture">
                </div>

                <div class="btn-wrapper">
                    <!-- Tombol Ganti Foto dengan ikon -->
                    <button type="button" onclick="document.getElementById('profile_picture').click();"
                        class="btn-custom btn-primary">
                        <i class="ion ion-image"></i>
                        Ganti Foto
                    </button>

                    <!-- Tombol Hapus Foto dengan ikon -->
                    <button type="submit" class="btn-custom btn-danger"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus foto profil?')">
                        <i class="ion ion-close"></i>
                        Hapus Foto
                    </button>
                </div>

                <div class="profile-instructions">
                    <p>Pilih foto profil yang mencerminkan identitas profesional Anda. Foto akan ditampilkan dalam format
                        lingkaran dengan ukuran optimal 180x180 piksel.</p>
                </div>
            </form>
        </div>
    </div>


@endsection
