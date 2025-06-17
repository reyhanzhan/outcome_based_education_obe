@extends('layouts_adminlte.app')

@section('title', 'Edit Kelas')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-center align-items-center flex-wrap">
                    <h3 class="card-title">Edit Kelas</h3>
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
                    <form action="{{ route('kelas.update', $kelas->id) }}" method="POST" class="text-center">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="tahun_kurikulum">Tahun Kurikulum</label>
                            <input type="text" name="tahun_kurikulum" id="tahun_kurikulum" class="form-control" value="{{ old('tahun_kurikulum', $kelas->tahun_kurikulum) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="kode_mk">Mata Kuliah</label>
                            <select name="kode_mk" id="kode_mk" class="form-control" required>
                                <option value="">Pilih Mata Kuliah</option>
                                @foreach ($matkul as $mk)
                                    <option value="{{ $mk->kode_mk }}" {{ old('kode_mk', $kelas->kode_mk) == $mk->kode_mk ? 'selected' : '' }}>
                                        {{ $mk->deskripsi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="periode">Periode</label>
                            <input type="text" name="periode" id="periode" class="form-control" value="{{ old('periode', $kelas->periode) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="nip_dosen">NIP Dosen</label>
                            <select name="nip_dosen" id="nip_dosen" class="form-control" required>
                                <option value="">Pilih Dosen</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->nip }}" {{ old('nip_dosen', $kelas->nip_dosen) == $user->nip ? 'selected' : '' }}>
                                        {{ $user->name }} <!-- Ganti nama sesuai kolom di users -->
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="btn-group mt-3">
                            <a href="{{ route('kelas.index') }}" class="btn btn-secondary mr-2" data-toggle="tooltip" title="Kembali ke daftar kelas">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" data-toggle="tooltip" title="Update data kelas">
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
            $('[data-toggle="tooltip"]').tooltip();
            $('#tahun_kurikulum').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);
            });
        });
    </script>
@endsection