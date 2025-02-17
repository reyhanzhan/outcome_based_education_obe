@extends('layouts_adminlte.app')

@section('title', 'Tambah Sub-CPMK')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Tambah Sub-CPMK Baru</h3>
            </div>
            <form action="{{ route('subcpmk.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="kode_subcpmk">Kode Sub-CPMK</label>
                        <input type="text" 
                               class="form-control @error('kode_subcpmk') is-invalid @enderror" 
                               id="kode_subcpmk" 
                               name="kode_subcpmk" 
                               value="{{ old('kode_subcpmk') }}" 
                               placeholder="Contoh: Sub-CPMK0111">
                        @error('kode_subcpmk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="uraian">Uraian Sub-CPMK</label>
                        <textarea class="form-control @error('uraian') is-invalid @enderror" 
                                  id="uraian" 
                                  name="uraian" 
                                  rows="3" 
                                  placeholder="Masukkan uraian Sub-CPMK">{{ old('uraian') }}</textarea>
                        @error('uraian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="cpmk_id">CPMK</label>
                        <select class="form-control @error('cpmk_id') is-invalid @enderror" id="cpmk_id" name="cpmk_id">
                            <option value="" disabled selected>Pilih CPMK</option>
                            @foreach ($cpmks as $cpmk)
                                <option value="{{ $cpmk->id }}" {{ old('cpmk_id') == $cpmk->id ? 'selected' : '' }}>
                                    {{ $cpmk->kode_cpmk }}
                                </option>
                            @endforeach
                        </select>
                        @error('cpmk_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('subcpmk.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
