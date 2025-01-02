@extends('layouts_baru.app')
@section('title', 'Daftar Capaian Pembelajaran Lulusan')
@section('content')

<div class="container py-4">
    <!-- Card Wrapper -->
    <div class="card p-4 shadow-lg border-0 mb-5"
        style="background-color: #ffffff; border-radius: 15px; border-top: 3px solid #198754 !important;">
        <div class="d-flex justify-content-between mb-3">
            <!-- Tambah Button -->
            <a href="{{ url('/CPL/create') }}" class="btn btn-success btn-custom">
                <i class="bi bi-plus-lg"></i> Tambah CPL
            </a>
        </div>

        <!-- Table Responsive -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped custom-table">
                <thead>
                    <tr>
                        <th style="color: #ffffff !important; background-color: #0052A2 !important; width: 5%;">NO</th>
                        <th style="color: #ffffff !important; background-color: #0052A2 !important;">CPL</th>
                        <th style="color: #ffffff !important; background-color: #0052A2 !important;">SINGKATAN CPL</th>
                        <th style="color: #ffffff !important; background-color: #0052A2 !important;">KATEGORI</th>
                        <th style="color: #ffffff !important; background-color: #0052A2 !important;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cpl as $index => $cples)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <!-- Kondisi untuk Mobile dan Desktop -->
                            <td>
                                <span class="d-none d-md-block">{{ $cples->deskripsi }}</span>
                                <span class="d-md-none text-truncate" style="max-width: 150px;">
                                    {{ $cples->deskripsi }}
                                </span>

                            </td>
                            <td>{{ $cples->kode_cpl }}</td>
                            <td>{{ $cples->kategori }}</td>
                            <td>
                                <!-- Tombol Edit dan Hapus Sejajar -->
                                <div class="action-buttons">
                                    <a href="{{ route('cpl.edit', $cples->id) }}" class="btn btn-warning btn-action btn-edit btn-sm">
                                        <i class="bi bi-pencil me-1"></i> Ubah
                                    </a>
                                    <form action="{{ route('cpl.destroy', $cples->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-action btn-delete btn-sm">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection

<style>
    /* General Table Styling */
    .custom-table th,
    .custom-table td {
        text-align: center;
        vertical-align: middle;
        padding: 1rem;
    }

    /* Truncate text for mobile */
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Table Responsiveness for Small Screens */
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }

        .custom-table th,
        .custom-table td {
            font-size: 0.85rem;
            padding: 0.6rem;
        }
    }

     /* Button Custom */
     .btn-custom {
        border-radius: 8px;
        font-weight: bold;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100px; /* Lebar sama */
        font-size: 0.9rem;
        padding: 0.4rem 0.6rem;
        transition: all 0.3s ease;
        border-radius: 5px;
        border: none;
    }

    /* Tombol Ubah */
    .btn-edit {
        background-color: #ffc107; /* Kuning */
        color: #212529;
        height: 32px;
    }

    .btn-edit:hover {
        background-color: #e0a800;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Tombol Hapus */
    .btn-delete {
        background-color: #dc3545; /* Merah */
        color: #fff;
    }

    .btn-delete:hover {
        background-color: #bd2130;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Tombol dalam satu baris */
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 0.5rem; /* Jarak antar tombol */
    }

    /* Card Styling */
    .card {
        border-radius: 15px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
</style>


