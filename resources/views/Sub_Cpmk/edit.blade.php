@extends('layouts_adminlte.app')

@section('title', 'Edit Sub-CPMK')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">Edit Sub-CPMK</h3>
            </div>
            <form action="{{ route('subcpmk.update', $subcpmk->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="kode_subcpmk">Kode Sub-CPMK</label>
                        <input type="text" 
                               class="form-control @error('kode_subcpmk') is-invalid @enderror" 
                               id="kode_subcpmk" 
                               name="kode_subcpmk" 
                               value="{{ old('kode_subcpmk', $subcpmk->kode_subcpmk) }}">
                        @error('kode_subcpmk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="uraian">Uraian Sub-CPMK</label>
                        <textarea class="form-control @error('uraian') is-invalid @enderror" 
                                  id="uraian" 
                                  name="uraian" 
                                  rows="3">{{ old('uraian', $subcpmk->uraian) }}</textarea>
                        @error('uraian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="cpmk_id">CPMK</label>
                        <select class="form-control @error('cpmk_id') is-invalid @enderror" id="cpmk_id" name="cpmk_id">
                            <option value="" disabled>Pilih CPMK</option>
                            @foreach ($cpmks as $cpmk)
                                <option value="{{ $cpmk->id }}" {{ $cpmk->id == old('cpmk_id', $subcpmk->cpmk_id) ? 'selected' : '' }}>
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
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update
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
