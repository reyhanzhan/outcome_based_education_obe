@extends('layouts_adminlte.app')

@section('title', 'Pilih Mahasiswa untuk Penilaian CPL')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pilih Mahasiswa</h3>
            </div>
            <div class="card-body">
                @if ($mahasiswas->isEmpty())
                    <div class="alert alert-warning">Tidak ada mahasiswa yang tersedia. Silakan tambahkan mahasiswa.</div>
                @else
                    <form action="{{ route('penilaian.cpl.index', ['mahasiswa_id' => ':mahasiswa_id']) }}" method="GET">
                        <div class="form-group">
                            <label for="mahasiswa_id">Pilih Mahasiswa:</label>
                            <select name="mahasiswa_id" id="mahasiswa_id" class="form-control" required>
                                @foreach ($mahasiswas as $mahasiswa)
                                    <option value="{{ $mahasiswa->id }}">{{ $mahasiswa->nama }} ({{ $mahasiswa->nim ?? 'NIM Tidak Ada' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Lanjutkan</button>
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
        $('form').submit(function(e) {
            e.preventDefault();
            var mahasiswa_id = $('#mahasiswa_id').val();
            if (mahasiswa_id) {
                window.location.href = "{{ route('penilaian.cpl.index', ['mahasiswa_id' => ':mahasiswa_id']) }}"
                    .replace(':mahasiswa_id', mahasiswa_id);
            } else {
                alert('Pilih mahasiswa terlebih dahulu!');
            }
        });
    });
</script>
@endsection
