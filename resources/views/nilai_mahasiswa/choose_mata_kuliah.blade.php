@extends('layouts_adminlte.app')

@section('title', 'Pilih Mahasiswa & Periode')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pilih Mahasiswa & Periode</h3>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}">
                        <div class="row">
                            <!-- Pilih Mahasiswa -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nim">1. Pilih Mahasiswa:</label>
                                    <select name="nim" id="nim" class="form-control">
                                        <option value="">-- Pilih Mahasiswa --</option>
                                        @foreach ($mahasiswas as $mhs)
                                            <option value="{{ $mhs->nim }}"
                                                {{ request('nim') == $mhs->nim ? 'selected' : '' }}>
                                                {{ $mhs->nim }} - {{ $mhs->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Pilih Periode -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periode">2. Pilih Periode:</label>
                                    <select name="periode" id="periode" class="form-control">
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach ($periodes as $periodeOption)
                                            <option value="{{ $periodeOption }}"
                                                {{ request('periode') == $periodeOption ? 'selected' : '' }}>
                                                {{ $periodeOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Tampilkan -->
                        <div class="text-right">
                            <button type="submit" class="btn btn-warning"><i class="fas fa-search"></i> Tampilkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Menampilkan peringatan jika tidak ada mata kuliah -->
            @if (isset($mks) && count($mks) === 0)

            
            
                <div class="alert alert-warning mt-3">
                    <strong>Peringatan!</strong> Mahasiswa ini tidak mengambil mata kuliah pada periode ini.
                </div>
            @endif

            <!-- Menampilkan Mata Kuliah jika ada -->
            @if (isset($mks) && count($mks) > 0)
                <div class="card mt-3">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title">Daftar Mata Kuliah Mahasiswa: {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }})</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Kode MK</th>
                                    <th>Nama MK</th>
                                    <th>Kelas</th>
                                    <th>Dosen</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mks as $mk)
                                    <tr>
                                        <td>{{ $mk->kode_mk }}</td>
                                        <td>{{ $mk->deskripsi }}</td>
                                        <td>{{ $mk->kelas }}</td>
                                        <td>{{ $mk->dosen }}</td>
                                        <td>
                                            <a href="{{ route('nilai.mahasiswa.index', ['nim' => $mahasiswa->nim, 'kode_mk' => $mk->kode_mk]) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Input Nilai
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // ✅ Inisialisasi Select2
            $('#periode').select2({
                placeholder: "-- Pilih Periode --",
                width: '100%'
            });

            // ✅ Auto-submit saat periode berubah
            $('#periode').on('change', function() {
                $('#filterForm').submit();
            });
        });
    </script>
@endsection
