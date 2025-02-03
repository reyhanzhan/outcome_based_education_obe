@extends('layouts_adminlte.app')

@section('title', 'Tambah Bahan Kajian')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Tambah Bahan KajianBaru</h3>
            </div>
            <form action="{{ route('bk.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="kode_bk">Kode Bahan Kajian</label>
                        <input type="text" 
                               class="form-control @error('kode_bk') is-invalid @enderror" 
                               id="kode_bk" 
                               name="kode_bk" 
                               value="{{ old('kode_bk') }}"
                               placeholder="Contoh: bk001">
                        @error('kode_bk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Bahan Kajian</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="3"
                                  placeholder="Masukkan deskripsi Bahan Kajian')">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('bk.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection