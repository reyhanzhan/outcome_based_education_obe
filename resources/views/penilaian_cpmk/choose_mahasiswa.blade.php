@extends('layouts_adminlte.app')

@section('title', 'Pilih Mahasiswa untuk Penilaian CPMK')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pilih Mahasiswa</h3>
                </div>
                <div class="card-body">
                    @if ($mahasiswas->isEmpty())
                        <div class="alert alert-warning">Tidak ada mahasiswa yang tersedia. Silakan tambahkan data mahasiswa terlebih dahulu.</div>
                    @else
                        <form action="{{ route('penilaian.cpmk.choose_mk', ['mahasiswa_id' => ':mahasiswa_id']) }}" method="GET" id="mahasiswaForm">
                            <div class="form-group">
                                <label for="mahasiswa_id">Pilih Mahasiswa:</label>
                                <select name="mahasiswa_id" id="mahasiswa_id" class="form-control select2" required>
                                    <option value="" disabled selected>-- Pilih Mahasiswa --</option>
                                    @foreach ($mahasiswas as $mahasiswa)
                                        <option value="{{ $mahasiswa->id }}">{{ $mahasiswa->nama }} ({{ $mahasiswa->nim ?? '' }})</option>
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
        // Inisialisasi Select2 untuk mahasiswa_id
        $('#mahasiswa_id').select2({
            placeholder: "-- Pilih Mahasiswa --",
            width: '100%',
            dropdownCssClass: 'custom-select2-dropdown',
            dropdownAutoWidth: true,
            minimumResultsForSearch: 1 // Mulai mencari setelah 1 karakter
        });

        // Handle form submission
        $('#mahasiswaForm').submit(function(e) {
            e.preventDefault();
            var mahasiswa_id = $('#mahasiswa_id').val();
            if (mahasiswa_id) {
                window.location.href = "{{ route('penilaian.cpmk.choose_mk', ['mahasiswa_id' => ':mahasiswa_id']) }}".replace(':mahasiswa_id', mahasiswa_id);
            } else {
                toastr.error('Pilih mahasiswa terlebih dahulu!');
            }
        });
    });
</script>
@endsection