@extends('layouts.app')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Daftar Mahasiswa</h3>
                    <a href="{{ route('mahasiswa.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Mahasiswa</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Periode Masuk</th>
                                    <th>Sistem Kuliah</th>
                                    <th>Jalur Penerimaan</th>
                                    <th>Gelombang Daftar</th>
                                    <th>Agama</th>
                                    <th>Kode Prodi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mahasiswas as $mahasiswa)
                                    <tr>
                                        <td>{{ $mahasiswa->nim }}</td>
                                        <td>{{ $mahasiswa->nama }}</td>
                                        <td>{{ $mahasiswa->periode_masuk }}</td>
                                        <td>{{ $mahasiswa->sistem_kuliah }}</td>
                                        <td>{{ $mahasiswa->jalur_penerimaan }}</td>
                                        <td>{{ $mahasiswa->gelombang_daftar }}</td>
                                        <td>{{ $mahasiswa->agama }}</td>
                                        <td>{{ $mahasiswa->kode_prodi }}</td>
                                        <td>
                                            <a href="{{ route('mahasiswa.edit', $mahasiswa->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                            <form action="{{ route('mahasiswa.destroy', $mahasiswa->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')"><i class="fas fa-trash"></i> Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data mahasiswa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection