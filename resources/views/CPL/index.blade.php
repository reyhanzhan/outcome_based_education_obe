@extends('layouts.app')
@section('title', 'Daftar Capaian Pembelajaran Lulusan')
@section('content')
    <style>
        .custom-table thead {
            color: #ffffff !important;
            background-color: #0052A2 !important;
        }

        .custom-table thead th {
            color: #ffffff !important;
            text-align: center;
            padding: 1rem;
        }
    </style>


    <div class="container">
        {{-- ini agar tabel tidak terlalu ke atas, ganti dengan  --}}
        <div class="container-title mb-4">
            <h1 class="display-5">Daftar CPL</h1>
            <p class="lead text-muted">Lihat informasi kode CPL dan deskripsi di bawah ini.</p>
        </div>

        <!-- Pembungkus kotak dengan shadow -->
        <div class="card p-4 shadow-lg border-0 mb-5 border-top"
            style="background-color: #ffffff; border-radius: 15px; border-top: 3px solid #198754!important;">
            <div class="d-flex justify-content-between mb-3">
                <!-- Tombol Tambah -->
                <a href="{{ url('/CPL/create') }}" type="button" class="btn btn-success custom-btn-green">
                    <i class="bi bi-plus-lg"></i> Tambah CPL
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="custom-thead">
                        <tr>
                            <th scope="col"
                                style="color: #ffffff !important; background-color: #0052A2 !important; width: 5%;">NO</th>
                            <th scope="col" style="color: #ffffff !important; background-color: #0052A2 !important;">CPL
                            </th>
                            <th scope="col"
                                style="color: #ffffff !important; background-color: #0052A2 !important; width: 20%;">
                                SINGKATAN CPL</th>
                            <th scope="col"
                                style="color: #ffffff !important; background-color: #0052A2 !important; width: 20%;">AKSI
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cpl as $index => $cples)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $cples->deskripsi }}</td>
                                <td>{{ $cples->kode_cpl }}</td>
                                <td>
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('cpl.edit', $cples->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Ubah
                                    </a>

                                    <!-- Form Hapus -->
                                    <form action="{{ route('cpl.destroy', $cples->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
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
    .table th,
    .table td {
        text-align: center;
        vertical-align: middle;
        padding: 1rem;
    }

    .container {
        overflow-x: hidden;
        max-width: 100%;
    }

    .table {
        width: 100%;
        table-layout: fixed;
    }

    .custom-btn-green:hover {
        background-color: #004d00;
        box-shadow: 0 5px #003300;
    }

    .custom-btn-green:active {
        transform: translateY(1px);
        box-shadow: 0 4px #006400;
    }

    .card {
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        /* Shadow lebih tebal */
        transition: all 0.3s ease;
        /* Animasi saat hover */
    }

    .card:hover {
        transform: translateY(-5px);
        /* Efek mengangkat saat di-hover */
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        /* Shadow lebih besar */
    }
</style>
