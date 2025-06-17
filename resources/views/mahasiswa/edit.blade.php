@extends('layouts_adminlte.app')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit Mahasiswa</h3>
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
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('mahasiswa.update', $mahasiswa->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nim">NIM</label>
                            <input type="text" name="nim" class="form-control" value="{{ old('nim', $mahasiswa->nim) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $mahasiswa->nama) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="periode_masuk">Periode Masuk</label>
                            <input type="text" name="periode_masuk" class="form-control" value="{{ old('periode_masuk', $mahasiswa->periode_masuk) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="sistem_kuliah">Sistem Kuliah</label>
                            <select name="sistem_kuliah" class="form-control" required>
                                <option value="Reguler Pagi" {{ old('sistem_kuliah', $mahasiswa->sistem_kuliah) == 'Reguler Pagi' ? 'selected' : '' }}>Reguler Pagi</option>
                                <option value="Reguler Sore" {{ old('sistem_kuliah', $mahasiswa->sistem_kuliah) == 'Reguler Sore' ? 'selected' : '' }}>Reguler Sore</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jalur_penerimaan">Jalur Penerimaan</label>
                            <select name="jalur_penerimaan" class="form-control" required>
                                <option value="SBMPTN" {{ old('jalur_penerimaan', $mahasiswa->jalur_penerimaan) == 'SBMPTN' ? 'selected' : '' }}>SBMPTN</option>
                                <option value="Seleksi Mandiri" {{ old('jalur_penerimaan', $mahasiswa->jalur_penerimaan) == 'Seleksi Mandiri' ? 'selected' : '' }}>Seleksi Mandiri</option>
                                <option value="Seleksi Mandiri PTS" {{ old('jalur_penerimaan', $mahasiswa->jalur_penerimaan) == 'Seleksi Mandiri PTS' ? 'selected' : '' }}>Seleksi Mandiri PTS</option>
                                <option value="Ujian Masuk Bersama PTS(UMB-PTS)" {{ old('jalur_penerimaan', $mahasiswa->jalur_penerimaan) == 'Ujian Masuk Bersama PTS(UMB-PTS)' ? 'selected' : '' }}>Ujian Masuk Bersama PTS(UMB-PTS)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gelombang_daftar">Gelombang Daftar</label>
                            <select name="gelombang_daftar" class="form-control" required>
                                <option value="K1-01" {{ old('gelombang_daftar', $mahasiswa->gelombang_daftar) == 'K1-01' ? 'selected' : '' }}>K1-01</option>
                                <option value="K1-02" {{ old('gelombang_daftar', $mahasiswa->gelombang_daftar) == 'K1-02' ? 'selected' : '' }}>K1-02</option>
                                <option value="K1-03" {{ old('gelombang_daftar', $mahasiswa->gelombang_daftar) == 'K1-03' ? 'selected' : '' }}>K1-03</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="agama">Agama</label>
                            <select name="agama" class="form-control" required>
                                <option value="Islam" {{ old('agama', $mahasiswa->agama) == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('agama', $mahasiswa->agama) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Hindu" {{ old('agama', $mahasiswa->agama) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('agama', $mahasiswa->agama) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Khonghucu" {{ old('agama', $mahasiswa->agama) == 'Khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kode_prodi">Kode Prodi</label>
                            <input type="text" name="kode_prodi" class="form-control" value="{{ $mahasiswa->kode_prodi }}" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection