@extends('layouts_adminlte.app')

@section('title', 'Penilaian CPL')

@section('css')
    <style>
        .card-header.bg-primary {
            background-color: #007bff !important; /* Biru primer AdminLTE */
            color: #fff;
        }

        .form-control:focus {
            border-color: #007bff; /* Biru untuk fokus input */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            background-color: #007bff; /* Biru primer AdminLTE */
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3; /* Biru gelap saat hover */
            border-color: #0056b3;
        }
    </style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Penilaian CPL woi</h3>
                </div>
                <div class="card-body">
                    @if ($mahasiswas->isEmpty())
                        <div class="alert alert-warning">Tidak ada mahasiswa yang tersedia. Silakan tambahkan data mahasiswa terlebih dahulu.</div>
                    @else
                        <form action="{{ route('penilaian.cpl.show', ['mahasiswa_id' => ':mahasiswa_id']) }}" method="GET">
                            <div class="form-group">
                                <label for="mahasiswa_id">Pilih Mahasiswa:</label>
                                <select name="mahasiswa_id" id="mahasiswa_id" class="form-control" required>
                                    @foreach ($mahasiswas as $mahasiswa)
                                        <option value="{{ $mahasiswa->id }}">{{ $mahasiswa->nama }} ({{ $mahasiswa->nim ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Lihat Penilaian CPL</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            if (typeof jQuery !== 'undefined') {
                $('form').submit(function(e) {
                    e.preventDefault();
                    var mahasiswa_id = $('#mahasiswa_id').val();
                    if (mahasiswa_id) {
                        window.location.href = "{{ route('penilaian.cpl.show', ['mahasiswa_id' => ':mahasiswa_id']) }}".replace(':mahasiswa_id', mahasiswa_id);
                    } else {
                        console.error('Pilih mahasiswa terlebih dahulu!');
                        toastr.error('Pilih mahasiswa terlebih dahulu!');
                    }
                });
            } else {
                console.error('jQuery tidak dimuat!');
                toastr.error('Gagal memuat halaman. Silakan perbarui browser.');
            }
        });
    </script>
@endsection