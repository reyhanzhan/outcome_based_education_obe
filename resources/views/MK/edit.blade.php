@extends('layouts_adminlte.app')

@section('title', 'Edit Mata Kuliah')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">Edit Mata Kuliah</h3>
            </div>
            <form action="{{ route('mk.update', $mk->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="kode_mk">Kode Mata Kuliah</label>
                        <input type="text" 
                               class="form-control @error('kode_mk') is-invalid @enderror" 
                               id="kode_mk" 
                               name="kode_mk" 
                               value="{{ old('kode_mk', $mk->kode_mk) }}">
                        @error('kode_mk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Mata Kuliah</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="3">{{ old('deskripsi', $mk->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sks">SKS</label>
                                <input type="number" 
                                       class="form-control @error('sks') is-invalid @enderror" 
                                       id="sks" 
                                       name="sks" 
                                       value="{{ old('sks', $mk->sks) }}"
                                       min="1"
                                       max="6">
                                @error('sks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="wptwp">WP/TWP</label>
                                <select class="form-control @error('wptwp') is-invalid @enderror" name="wptwp" id="wptwp">
                                    <option value="" selected disabled>Pilih TWP/WP</option>
                                    <option value="TWP">TWP</option>
                                    <option value="WP">WP</option>
                                </select>
                                @error('wptwp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('mk.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection