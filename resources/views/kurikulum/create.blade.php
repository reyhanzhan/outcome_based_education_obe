@extends('layouts_adminlte.app')

@section('title', 'Tambah Kurikulum')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Kurikulum</h3>
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
                    <form action="{{ route('kurikulum.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Tahun</label>
                            <input type="text" name="tahun" class="form-control" placeholder="YYYY" required>
                        </div>
                        <div class="form-group">
                            <label>Mata Kuliah</label>
                            <select name="kode_mk" class="form-control" required>
                                <option value="">Pilih Mata Kuliah</option>
                                @foreach ($matkul as $mk)
                                    <option value="{{ $mk->kode_mk }}">{{ $mk->deskripsi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Semester</label>
                            <input type="number" name="semester" class="form-control" placeholder="Opsional">
                        </div>
                        <a href="{{ route('kurikulum.index') }}" class="btn btn-secondary mr-2" data-toggle="tooltip" title="Kembali ke daftar kurikulum">
                                <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection