@extends('layouts.app')
@section('title', 'Daftar Capaian Pembelajaran Lulusan')
@section('content')
    <div class="container">
        <div class="container-title mb-4">
            <h1 class="display-5">Daftar PL</h1>
            <p class="lead text-muted">Lihat informasi kode PL, deskripsi, dan unsur di bawah ini.</p>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <!-- Tombol Tambah PL -->
            <a href="{{ url('/PL/create') }}" type="button" class="btn btn-success btn-block custom-btn-green">
                <i class="bi bi-plus-lg"></i> Tambah PL
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th scope="col" style="width: 5%">NO</th>
                        <th scope="col">PL</th>
                        <th scope="col" style="width: 20%">SINGKATAN PL</th>
                        <th scope="col" style="width: 20%">UNSUR</th>
                        <th scope="col" style="width: 20%">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pl as $index => $ples)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $ples->deskripsi }}</td>
                            <td>{{ $ples->kode_pl }}</td>
                            <td>{{ $ples->unsur }}</td>
                            <td>
                                <!-- Tombol Edit dengan Ikon -->
                                <a href="{{ route('pl.edit', $ples->id) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil"></i> Ubah
                                </a>
                            
                                <!-- Form Hapus dengan Ikon -->
                                <form action="{{ route('pl.destroy', $ples->id) }}" method="POST" class="d-inline"
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
@endsection

<style>
    .table th, .table td {
        text-align: center; /* Mengatur teks di tengah secara horizontal */
        vertical-align: middle; /* Mengatur teks di tengah secara vertikal */
    }

    .container {
        overflow-x: hidden; /* Pastikan tidak ada scroll horizontal pada container */
        max-width: 100%;
    }

    .container-fluid {
        max-width: 100%; /* Pastikan container mengambil seluruh lebar viewport */
        padding: 0 2rem; /* Opsional: menambahkan padding untuk estetika */
    }

    .table {
        width: 100%; /* Pastikan tabel mengambil seluruh lebar container */
    }

    .table th, .table td {
        padding: 1rem; /* Atur padding untuk lebih banyak ruang */
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        table-layout: fixed; /* Menggunakan fixed layout agar lebar kolom lebih stabil */
    }

    .table th, .table td {
        word-wrap: break-word; /* Menangani teks yang terlalu panjang */
        max-width: 250px; /* Atur lebar maksimal kolom jika perlu */
    }

    .custom-btn-green:hover {
        background-color: #004d00;
        box-shadow: 0 5px #003300;
    }

    .custom-btn-green:active {
        transform: translateY(1px);
        box-shadow: 0 4px #006400;
    }
</style>
