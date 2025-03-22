@extends('layouts_adminlte.app')

@section('title', 'Pilih Mata Kuliah untuk Penilaian CPMK')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h3 class="card-title">Pilih Mata Kuliah untuk {{ $mahasiswa->nama }}</h3>
                <a href="{{ route('penilaian.cpmk.choose_mahasiswa') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Pilih Mahasiswa</a>
            </div>
            <div class="card-body">
                @if ($mks->isEmpty())
                    <div class="alert alert-warning">Tidak ada mata kuliah yang tersedia. Silakan tambahkan data mata kuliah terlebih dahulu.</div>
                @else
                    <form action="{{ route('penilaian.cpmk.index', ['mk_id' => ':mk_id']) }}" method="GET" id="mkForm">
                        <input type="hidden" name="mahasiswa_id" value="{{ $mahasiswa->id }}">
                        <div class="form-group">
                            <label for="mk_id">Pilih Mata Kuliah:</label>
                            <select name="mk_id" id="mk_id" class="form-control select2" required>
                                <option value="" disabled selected>-- Pilih Mata Kuliah --</option>
                                @foreach ($mks as $mk)
                                    <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->deskripsi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Lihat Penilaian CPMK</button>
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
        // Inisialisasi Select2 untuk mk_id
        $('#mk_id').select2({
            placeholder: "-- Pilih Mata Kuliah --",
            width: '100%',
            dropdownCssClass: 'custom-select2-dropdown',
            dropdownAutoWidth: true,
            minimumResultsForSearch: 1 // Mulai mencari setelah 1 karakter
        });

        // Handle form submission
        $('#mkForm').submit(function(e) {
            e.preventDefault();
            var mk_id = $('#mk_id').val();
            if (mk_id) {
                var mahasiswa_id = $('input[name="mahasiswa_id"]').val();
                window.location.href = "{{ route('penilaian.cpmk.index', ['mk_id' => ':mk_id']) }}".replace(':mk_id', mk_id) + '?mahasiswa_id=' + mahasiswa_id;
            } else {
                toastr.error('Pilih mata kuliah terlebih dahulu!');
            }
        });
    });
</script>
@endsection