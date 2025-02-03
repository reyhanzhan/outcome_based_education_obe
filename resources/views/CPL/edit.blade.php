@extends('layouts_adminlte.app')

@section('title', 'Capaian Profil Lulusan')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">Capaian Profil Lulusan</h3>
            </div>
            <form action="{{ route('cpl.update', $cpl->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="kode_cpl">Kode Mata Kuliah</label>
                        <input type="text" 
                               class="form-control @error('kode_cpl') is-invalid @enderror" 
                               id="kode_cpl" 
                               name="kode_cpl" 
                               value="{{ old('kode_cpl', $cpl->kode_cpl) }}"
                               readonly>
                        @error('kode_cpl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Mata Kuliah</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="3">{{ old('deskripsi', $cpl->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('cpl.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection