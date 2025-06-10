@extends('layouts_adminlte.app')

@section('title', 'Penilaian CPMK')

@section('css')
<style>
    .card-header.bg-primary {
        background-color: #007bff !important;
        color: #fff;
    }

    .table-bordered th, .table-bordered td {
        vertical-align: middle;
        text-align: center;
    }

    .bobot-highlight {
        background-color: #e9ecef;
        font-weight: bold;
        color: #000000;
    }

    .bg-light {
        background-color: #f8f9fa;
    }

    .below-min {
        color: #dc3545;
        font-weight: bold;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #5a6268;
    }

    .calculation-note {
        font-size: 0.9em;
        color: #6c757d;
        font-style: italic;
        margin-top: 10px;
    }

    .debug-info {
        font-size: 0.85em;
        color: #6c757d;
        margin-top: 15px;
        border: 1px solid #dee2e6;
        padding: 10px;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    Penilaian CPMK untuk {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }}) pada {{ $mk->kode_mk }} - {{ $mk->deskripsi }}
                </h3>
                <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ $periode }}&kelas={{ $kelasInput }}"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Mahasiswa
                </a>
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <strong>Periode:</strong> {{ $periode }} <br>
                    <strong>Kelas:</strong> {{ $kelasInput }} <br>
                    <div class="calculation-note">
                        *Catatan: Nilai akhir setiap CPMK dihitung dengan rumus: (Nilai Input × Bobot) / 100. Nilai total mata kuliah adalah jumlah nilai akhir semua CPMK. Nilai input di bawah standar minimum ({{ $minStandard }}) ditandai merah.
                    </div>
                </div>

                @if ($cpmks->isEmpty())
                    <div class="alert alert-warning">
                        Tidak ada CPMK yang terkait dengan mata kuliah ini.
                    </div>
                @else
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="bg-light" style="width: 15%;">Nama Mahasiswa</th>
                                    @foreach ($cpmks as $cpmk)
                                        <th class="bobot-highlight">
                                            {{ $cpmk->kode_cpmk }} (Bobot: {{ $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0 }}%)
                                        </th>
                                    @endforeach
                                    <th class="bg-light">Nilai Total MK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="bg-light">{{ $mahasiswa->nama }}</td>
                                    @php
                                        $totalScore = 0;
                                    @endphp
                                    @foreach ($cpmks as $cpmk)
                                        <td>
                                            @php
                                                $nilaiInput = $nilaiCpmks[$cpmk->id] ?? 0;
                                                $bobot = $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0;
                                                $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                                                $totalScore += $nilaiAkhir;
                                            @endphp
                                            <span class="{{ $nilaiInput < $minStandard ? 'below-min' : '' }}">
                                                {{ number_format($nilaiAkhir, 2) }}
                                            </span>
                                        </td>
                                    @endforeach
                                    <td class="bg-light">
                                        {{ number_format($totalScore, 0) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Informasi Debugging -->
                    <div class="debug-info">
                        <strong>Detail Perhitungan:</strong><br>
                        @foreach ($cpmks as $cpmk)
                            @php
                                $nilaiInput = $nilaiCpmks[$cpmk->id] ?? 0;
                                $bobot = $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0;
                                $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                            @endphp
                            {{ $cpmk->kode_cpmk }}: Nilai Input = {{ $nilaiInput }}, Bobot = {{ $bobot }}%, Kontribusi = {{ number_format($nilaiAkhir, 2) }}<br>
                        @endforeach
                        <strong>Total Nilai MK: {{ number_format($totalScore, 0) }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Tidak ada script tambahan diperlukan
    });
</script>
@endsection