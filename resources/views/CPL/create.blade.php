@extends('layouts_baru.app')
@section('title', 'Tambah Capaian Profil Lulusan')
@section('content')
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="row w-100">
            <div class="col-md-8 col-lg-6 mx-auto">
                <div class="form-card shadow-lg p-4 rounded-4 bg-white">
                    <h2 class="text-center mb-3 text-dark font-weight-bold">Tambah Capaian Profil Lulusan</h2>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('cpl.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf <!-- Token CSRF untuk keamanan -->

                        <!-- Input Kode PL -->
                        <div class="form-group mb-4">
                            <label for="kode_cpl" class="form-label d-flex align-items-center text-primary fw-semibold">
                                <i class="bi bi-code-slash me-2"></i> Kode CPL<span class="text-danger">*</span>
                            </label>
                            <div class="input-container">
                                <input type="text" name="kode_cpl" class="custom-input form-control" id="kode_cpl"
                                    placeholder="CPL..." required>
                            </div>
                        </div>

                        <!-- Input Deskripsi -->
                        <div class="form-group mb-4">
                            <label for="deskripsi" class="form-label d-flex align-items-center text-primary fw-semibold">
                                <i class="bi bi-card-text me-2"></i> Deskripsi<span class="text-danger">*</span>
                            </label>
                            <textarea name="deskripsi" class="custom-input form-control" id="deskripsi" rows="3"
                                placeholder="Masukkan deskripsi..." required></textarea>
                        </div>

                        <!-- Input Kategori -->
                        <div class="form-group mb-4">
                            <label for="kategori" class="form-label d-flex align-items-center text-primary fw-semibold">
                                <i class="bi bi-list-check me-2"></i> Kategori<span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kategori" class="custom-input form-control" id="kategori"
                                placeholder="Masukkan kategori..." required>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                            <a href="{{ url('CPL/index') }}" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                            </a>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    /* General styling */
    .min-vh-100 {
        min-height: 100vh;
    }

    .form-card {
        background-color: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #e0e4e8;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
    }

    /* Input styling */
    .input-container {
        position: relative;
    }

    .icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 1.2rem;
    }

    .custom-input {
        padding-left: 2.5rem;
    }

    /* Button styling */
    .btn {
        border-radius: 8px;
        font-weight: bold;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .form-card {
            padding: 20px;
        }
    }
</style>
