@extends('layouts.app')
@section('title', 'Tambah Profil Lulusan')
@section('content')
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="form-card shadow-lg p-4 rounded-4 bg-white">
            <h2 class="text-center mb-3 text-dark font-weight-bold">Tambah Data Capaian Profil Lulusan</h2>
            <!-- Tampilkan pesan sukses jika ada -->
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('cpl.store') }}" method="POST" enctype="multipart/form-data">
                @csrf <!-- Token CSRF untuk keamanan -->

                <!-- Input Kode PL -->
                <div class="form-group mb-4 position-relative">
                    <span class="input-icon"><i class="bi bi-code-slash"></i></span>
                    <label for="kode_cpl" class="form-label" style="color: #8293e4; font-weight: bold;">
                        Kode CPL<span class="text-danger">*</span>
                    </label>
                    <input type="text" name="kode_cpl" class="form-control" id="kode_cpl"
                        placeholder="Masukkan kode CPL..." required>
                </div>

                <!-- Input Deskripsi -->
                <div class="form-group mb-4 position-relative">
                    <span class="input-icon"><i class="bi bi-card-text"></i></span>
                    <label for="deskripsi" class="form-label" style="color: #8293e4; font-weight: bold;">
                        Deskripsi<span class="text-danger">*</span>
                    </label>
                    <textarea name="deskripsi" class="form-control" id="deskripsi" rows="3" placeholder="Masukkan deskripsi..."
                        required></textarea>
                </div>

                <!-- Input kategori -->
                <div class="form-group mb-4 position-relative">
                    <span class="input-icon"><i class="bi bi-list-check"></i></span>
                    <label for="kategori" class="form-label">kategori</label>
                    <input type="text" name="kategori" class="form-control" id="unsur" placeholder="Masukkan kategori..." required>
                </div>

                <!-- Tombol submit -->
                <div class="d-flex gap-3 mt-4">
                    <!-- Tombol Kembali ke Daftar -->
                    <a href="{{ url('CPL/index') }}" type="button" class="btn btn-primary btn-block custom-btn-blue">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                    </a>


                    <!-- Tombol Simpan -->
                    <button type="submit" class="justify-content-center btn btn-success btn-block custom-btn-green">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection

<style>
    /* Center the form in the middle of the page */
    .min-vh-100 {
        min-height: 100vh;

    }

    /* Card styling for form */
    .form-card {
        width: 100%;
        max-width: 600px;
        /* Menyesuaikan ukuran maksimal card */
        background-color: #357ab8;
        margin: auto;
        border-radius: 12px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.15);
        padding: 30px;
        border: 1px solid #e0e4e8;
    }


    /* Title styling */
    .text-primary {
        color: #4a90e2;
        font-size: 1.6rem;
        font-weight: 700;
    }

    /* Input styling */
    .input-container {
        display: flex;
        align-items: center;
        position: relative;
    }

    /* Icon styling */
    .icon {
        position: absolute;
        left: 12px;
        font-size: 1.3rem;
        color: #adb5bd;
    }

    /* Custom input fields */
    .custom-input {
        width: 100%;
        padding-left: 2.5rem;
        border: 1px solid #ced4da;
        border-radius: 8px;
        height: 40px;
        font-size: 0.95rem;
        color: #495057;
        background-color: #f8f9fa;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .custom-input:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 5px rgba(74, 144, 226, 0.25);
        background-color: #ffffff;
    }

    /* Custom button styling */
    .custom-btn {
        background-color: #008000;
        /* Hijau */
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: bold;
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        box-shadow: 0 6px #006400;
        /* Bayangan tombol */
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .custom-btn i {
        margin-right: 10px;
        /* Jarak ikon ke teks */
    }

    .custom-btn-green:hover {
        background-color: #004d00;
        /* Warna hijau gelap saat di-hover */
        box-shadow: 0 5px #003300;
        /* Bayangan saat di-hover */
    }

    .custom-btn-green:active {
        transform: translateY(1px);
        box-shadow: 0 4px #006400;
        /* Bayangan saat tombol ditekan */
    }

    .custom-btn-blue:hover {
        background-color: #28527a;
        /* Warna biru gelap saat di-hover */
        box-shadow: 0 5px #1f3d5c;
        /* Bayangan saat di-hover */
    }

    .custom-btn-blue:active {
        transform: translateY(1px);
        box-shadow: 0 4px #2a5d86;
        /* Bayangan saat tombol ditekan */
    }

    .custom-btn:active {
        transform: translateY(1px);
        box-shadow: 0 4px #006400;
        /* Bayangan saat tombol ditekan */
    }

    /* Responsive form card */
    @media (max-width: 576px) {
        .form-card {
            padding: 20px;
        }

        .text-primary {
            font-size: 1.4rem;
        }

        .custom-input {
            padding-left: 2rem;
        }

        .icon {
            font-size: 1.1rem;
        }
    }
</style>
