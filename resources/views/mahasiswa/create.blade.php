@extends('layouts_adminlte.app')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Tambah Mahasiswa</h3>
                    <a href="{{ route('mahasiswa.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
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
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('mahasiswa.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="nim">NIM</label>
                            <input type="text" name="nim" class="form-control" value="{{ old('nim') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="periode_masuk">Periode Masuk</label>
                            <input type="text" name="periode_masuk" class="form-control" value="{{ old('periode_masuk') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="sistem_kuliah">Sistem Kuliah</label>
                            <select name="sistem_kuliah" class="form-control" required>
                                <option value="Reguler Pagi" {{ old('sistem_kuliah') == 'Reguler Pagi' ? 'selected' : '' }}>Reguler Pagi</option>
                                <option value="Reguler Sore" {{ old('sistem_kuliah') == 'Reguler Sore' ? 'selected' : '' }}>Reguler Sore</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jalur_penerimaan">Jalur Penerimaan</label>
                            <select name="jalur_penerimaan" class="form-control" required>
                                <option value="SBMPTN" {{ old('jalur_penerimaan') == 'SBMPTN' ? 'selected' : '' }}>SBMPTN</option>
                                <option value="Seleksi Mandiri" {{ old('jalur_penerimaan') == 'Seleksi Mandiri' ? 'selected' : '' }}>Seleksi Mandiri</option>
                                <option value="Seleksi Mandiri PTS" {{ old('jalur_penerimaan') == 'Seleksi Mandiri PTS' ? 'selected' : '' }}>Seleksi Mandiri PTS</option>
                                <option value="Ujian Masuk Bersama PTS(UMB-PTS)" {{ old('jalur_penerimaan') == 'Ujian Masuk Bersama PTS(UMB-PTS)' ? 'selected' : '' }}>Ujian Masuk Bersama PTS (UMB-PTS)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gelombang_daftar">Gelombang Daftar</label>
                            <select name="gelombang_daftar" class="form-control" required>
                                <option value="K1-01" {{ old('gelombang_daftar') == 'K1-01' ? 'selected' : '' }}>K1-01</option>
                                <option value="K1-02" {{ old('gelombang_daftar') == 'K1-02' ? 'selected' : '' }}>K1-02</option>
                                <option value="K1-03" {{ old('gelombang_daftar') == 'K1-03' ? 'selected' : '' }}>K1-03</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="agama">Agama</label>
                            <select name="agama" class="form-control" required>
                                <option value="Islam" {{ old('agama') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('agama') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Hindu" {{ old('agama') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('agama') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Khonghucu" {{ old('agama') == 'Khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                            </select>
                        </div>
                        <input type="hidden" name="kode_prodi" value="{{ $kodeProdi }}">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection