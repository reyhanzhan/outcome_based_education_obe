@extends('layouts_adminlte.app')

@section('title', 'Edit Kurikulum')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-center align-items-center flex-wrap">
                    <h3 class="card-title">Edit Kurikulum</h3>
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
                    <form action="{{ route('kurikulum.update', $kurikulum->id) }}" method="POST" class="text-center">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="tahun">Tahun</label>
                            <input type="text" name="tahun" id="tahun" class="form-control" value="{{ old('tahun', $kurikulum->tahun) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="kode_mk">Mata Kuliah</label>
                            <select name="kode_mk" id="kode_mk" class="form-control" required>
                                <option value="">Pilih Mata Kuliah</option>
                                @foreach ($matkul as $mk)
                                    <option value="{{ $mk->kode_mk }}" {{ old('kode_mk', $kurikulum->kode_mk) == $mk->kode_mk ? 'selected' : '' }}>
                                        {{ $mk->deskripsi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="semester">Semester</label>
                            <input type="number" name="semester" id="semester" class="form-control" value="{{ old('semester', $kurikulum->semester) }}">
                        </div>
                        <div class="btn-group mt-3">
                            <a href="{{ route('kurikulum.index') }}" class="btn btn-secondary mr-2" data-toggle="tooltip" title="Kembali ke daftar kurikulum">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" data-toggle="tooltip" title="Update data kurikulum">
                                <i class="fas fa-edit"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Aktifkan tooltip
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection