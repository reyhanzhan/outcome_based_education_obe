@extends('layouts_adminlte.app')

@section('title', 'Capaian Profil Lulusan')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">Edit Capaian Profil Lulusan</h3>
            </div>
            <form action="{{ route('cpl.update', $cpl->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="kode_cpl">Kode Capaian Profil Lulusan</label>
                        <input type="text" 
                               class="form-control @error('kode_cpl') is-invalid @enderror" 
                               id="kode_cpl" 
                               name="kode_cpl" 
                               value="{{ old('kode_cpl', $cpl->kode_cpl) }}"
                               placeholder="Contoh: cpl001">
                        @error('kode_cpl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Capaian Profil Lulusan</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="3"
                                  placeholder="Masukkan deskripsi capaian profil lulusan">{{ old('deskripsi', $cpl->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <input type="text" 
                               class="form-control @error('kategori') is-invalid @enderror" 
                               id="kategori" 
                               name="kategori" 
                               value="{{ old('kategori', $cpl->kategori) }}"
                               placeholder="Masukkan kategori">
                        @error('kategori')
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

@if (session('error'))
    <script>
        toastr.error('{{ session('error') }}', {
            position: 'top-right',
            timeOut: 5000,
            progressBar: true,
            iconClass: 'toast-error'
        });
    </script>
@endif

@if (session('success'))
    <script>
        toastr.success('{{ session('success') }}', {
            position: 'top-right',
            timeOut: 5000,
            progressBar: true,
            iconClass: 'toast-success'
        });
    </script>
@endif
@endsection