@extends('layouts_adminlte.app')

@section('title', 'Pilih Mata Kuliah untuk Input Nilai Mahasiswa')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pilih Mata Kuliah</h3>
            </div>
            <div class="card-body">
                @if ($mks->isEmpty())
                    <div class="alert alert-warning">Tidak ada mata kuliah yang tersedia. Silakan tambahkan data mata kuliah terlebih dahulu.</div>
                @else
                    <form action="{{ route('nilai.mahasiswa.index', ['mk_id' => ':mk_id']) }}" method="GET">
                        <div class="form-group">
                            <label for="mk_id">Pilih Mata Kuliah:</label>
                            <select name="mk_id" id="mk_id" class="form-control" required>
                                @foreach ($mks as $mk)
                                    <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->deskripsi }}</option>
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
        // Pastikan form mengarahkan ke rute yang benar
        if (typeof jQuery !== 'undefined') {
            $('form').submit(function(e) {
                e.preventDefault();
                var mk_id = $('#mk_id').val();
                window.location.href = "{{ route('nilai.mahasiswa.index', ['mk_id' => ':mk_id']) }}".replace(':mk_id', mk_id);
            });
        } else {
            console.error('jQuery tidak dimuat!');
        }
    });
</script>
@endsection