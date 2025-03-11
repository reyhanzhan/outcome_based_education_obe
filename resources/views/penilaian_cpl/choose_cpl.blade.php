@extends('layouts_adminlte.app')

@section('title', 'Pilih CPL untuk Penilaian')


@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pilih CPL untuk {{ $mahasiswa->nama }}</h3>
                <a href="{{ route('penilaian.cpl.choose_mahasiswa') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                @if ($cpls->isEmpty())
                    <div class="alert alert-warning">Tidak ada CPL yang tersedia untuk mahasiswa ini.</div>
                @else
                    <form id="chooseCplForm" method="GET">
                        <div class="form-group">
                            <label for="cpl_id">Pilih CPL:</label>
                            <select name="cpl_id" id="cpl_id" class="form-control" required>
                                @foreach ($cpls as $cpl)
                                    <option value="{{ $cpl->id }}">{{ $cpl->kode_cpl }} - {{ Str::limit($cpl->deskripsi, 150) }}</option>
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
        if (typeof jQuery !== 'undefined') {
            $('#chooseCplForm').submit(function(e) {
                e.preventDefault();
                var cpl_id = $('#cpl_id').val();
                if (cpl_id) {
                    window.location.href = "{{ route('penilaian.cpl.index', ['mahasiswa_id' => $mahasiswa->id, 'cpl_id' => ':cpl_id']) }}".replace(':cpl_id', cpl_id);
                } else {
                    console.error('Pilih CPL terlebih dahulu!');
                    toastr.error('Pilih CPL terlebih dahulu!');
                }
            });
        } else {
            console.error('jQuery tidak dimuat!');
            toastr.error('Gagal memuat halaman. Silakan perbarui browser.');
        }
    });
</script>
@endsection