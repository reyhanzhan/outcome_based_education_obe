@extends('layouts_adminlte.app')

@section('title', 'Tambah Capaian Pembelajaran Mata Kuliah')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Tambah Capaian Pembelajaran Mata Kuliah Baru</h3>
            </div>
            <form action="{{ route('cpmk.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="kode_cpmk">Kode Capaian Pembelajaran Mata Kuliah</label>
                        <input type="text" 
                               class="form-control @error('kode_cpmk') is-invalid @enderror" 
                               id="kode_cpmk" 
                               name="kode_cpmk" 
                               value="{{ old('kode_cpmk') }}"
                               placeholder="Contoh: cpmk001">
                        @error('kode_cpmk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Capaian Pembelajaran Mata Kuliah</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="3"
                                  placeholder="Masukkan deskripsi mata kuliah">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('cpmk.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection