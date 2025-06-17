@extends('layouts_adminlte.app')

@section('title', 'Tambah KRS')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah KRS</h3>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('krs.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Periode</label>
                            <input type="text" name="periode" class="form-control" placeholder="Contoh: 2024/2025 Ganjil" required>
                        </div>
                        <div class="form-group">
                            <label>Kode MK</label>
                            <select name="kode_mk" class="form-control" required>
                                <option value="">Pilih Mata Kuliah</option>
                                @foreach ($matkul as $mk)
                                    <option value="{{ $mk->kode_mk }}">{{ $mk->deskripsi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tahun</label>
                            <input type="text" name="tahun" class="form-control" placeholder="YYYY" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Kelas</label>
                            <input type="text" name="nama_kelas" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>NIM Mahasiswa</label>
                            <select name="nim" class="form-control" required>
                                <option value="">Pilih Mahasiswa</option>
                                @foreach ($mahasiswa as $m)
                                    <option value="{{ $m->nim }}">{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a href="{{ route('krs.index') }}" class="btn btn-secondary mr-2" data-toggle="tooltip" title="Kembali ke daftar krs">
                                <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection