@extends('layouts_adminlte.app')

@section('title', 'Edit Capaian Pembelajaran Mata Kuliah')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">Edit Capaian Pembelajaran Mata Kuliah</h3>
            </div>
            <form action="{{ route('cpmk.update', $cpmk->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="kode_cpmk">Kode Capaian Pembelajaran Mata Kuliah</label>
                        <input type="text" 
                               class="form-control @error('kode_cpmk') is-invalid @enderror" 
                               id="kode_cpmk" 
                               name="kode_cpmk" 
                               value="{{ old('kode_cpmk', $cpmk->kode_cpmk) }}"
                               readonly>
                        @error('kode_cpmk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Capaian Pembelajaran Mata Kuliah</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="3">{{ old('deskripsi', $cpmk->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update
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