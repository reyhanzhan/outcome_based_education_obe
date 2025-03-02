@extends('layouts_adminlte.app')

@section('title', 'Pilih Mata Kuliah untuk Penilaian CPMK {{ $mahasiswa->nama }}')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pilih Mata Kuliah untuk {{ $mahasiswa->nama }}</h3>
            </div>
            <div class="card-body">
                @if ($mks->isEmpty())
                    <div class="alert alert-warning">Tidak ada mata kuliah yang tersedia. Silakan tambahkan data mata kuliah terlebih dahulu.</div>
                @else
                    <form action="{{ route('penilaian.cpmk.radar', ['mahasiswa_id' => $mahasiswa->id, 'mk_id' => ':mk_id']) }}" method="GET">
                        <div class="form-group">
                            <label for="mk_id">Pilih Mata Kuliah:</label>
                            <select name="mk_id" id="mk_id" class="form-control" required>
                                @foreach ($mks as $mk)
                                    <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->deskripsi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Lihat Grafik Radar</button>
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
                var mk_id = $('#mk_id').val();
                if (mk_id) {
                    window.location.href = "{{ route('penilaian.cpmk.radar', ['mahasiswa_id' => $mahasiswa->id, 'mk_id' => ':mk_id']) }}".replace(':mk_id', mk_id);
                } else {
                    console.error('Pilih mata kuliah terlebih dahulu!');
                    toastr.error('Pilih mata kuliah terlebih dahulu!');
                }
            });
        } else {
            console.error('jQuery tidak dimuat!');
            toastr.error('Gagal memuat halaman. Silakan perbarui browser.');
        }
    });
</script>
@endsection