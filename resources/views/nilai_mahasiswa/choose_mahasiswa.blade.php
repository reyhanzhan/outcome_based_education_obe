@extends('layouts_adminlte.app')

@section('title', 'Pilih Mahasiswa untuk Input Nilai')

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
                    <form id="mahasiswaForm">
                        <div class="form-group">
                            <label for="nim">Pilih Mahasiswa:</label>
                            <select name="nim" id="nim" class="form-control">
                                <option value="">-- Pilih Mahasiswa --</option> 
                                @foreach ($mahasiswas as $mahasiswa)
                                    <option value="{{ $mahasiswa->nim }}">{{ $mahasiswa->nim }} - {{ $mahasiswa->nama }}</option>
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
        // ✅ Inisialisasi Select2 dengan fitur pencarian
        $('#nim').select2({
            placeholder: "-- Pilih Mahasiswa --",
            allowClear: false, // Tidak ada tombol "X" untuk hapus pilihan
            width: '100%'
        });

        // ✅ Handle form submit agar langsung navigate ke halaman input nilai
        $('#mahasiswaForm').submit(function(e) {
            e.preventDefault();
            var nim = $('#nim').val();
            if (!nim) {
                toastr.error("Pilih mahasiswa terlebih dahulu!", {
                    position: 'top-right',
                    timeOut: 3000,
                    progressBar: true
                });
                return;
            }
            window.location.href = "{{ route('nilai.mahasiswa.choose_mata_kuliah', ['nim' => ':nim']) }}".replace(':nim', nim);
        });
    });
</script>
@endsection
