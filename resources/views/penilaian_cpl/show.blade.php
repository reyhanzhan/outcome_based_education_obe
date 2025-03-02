@extends('layouts_adminlte.app')

@section('title', 'Penilaian CPL {{ $mahasiswa->nama }}')

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
        background-color: #e9ecef; /* Abu-abu muda untuk bobot */
        font-weight: bold;
        color: #007bff; /* Biru untuk kontras */
    }

    .bg-light {
        background-color: #f8f9fa; /* Abu-abu sangat muda untuk kontras */
    }
</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h3 class="card-title">Penilaian CPL {{ $mahasiswa->nama }}</h3>
                <a href="{{ route('penilaian.cpl.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Pilih Mahasiswa</a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="bg-light" style="width: 15%;">Kode CPL</th>
                                <th>Deskripsi</th>
                                <th>Bobot CPMK (%)</th>
                                <th>Nilai CPL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cpls as $cpl)
                                <tr>
                                    <td class="bg-light">{{ $cpl->kode_cpl }}</td>
                                    <td>{{ $cpl->deskripsi }}</td>
                                    <td class="bobot-highlight">
                                        @foreach ($cpl->cpmks as $cpmk)
                                            {{ $cpmk->kode_cpmk }} ({{ $cpmk->pivot->bobot }}%)<br>
                                        @endforeach
                                    </td>
                                    <td class="bg-light">{{ number_format($cplScores[$cpl->id] ?? 0, 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <h4>Detail Nilai CPMK yang Berkontribusi ke CPL:</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="bg-light">Kode CPMK</th>
                                    <th>Deskripsi</th>
                                    <th>Nilai Mahasiswa</th>
                                    <th>Bobot MK (%)</th>
                                    <th>Bobot CPL-CPMK (%)</th>
                                    <th>Kontribusi ke CPL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cpls as $cpl)
                                    @foreach ($cpl->cpmks as $cpmk)
                                        <?php
                                        $nilaiCpmk = NilaiCpmk::where('mahasiswa_id', $mahasiswa->id)
                                                            ->where('cpmk_id', $cpmk->id)
                                                            ->first();
                                        $bobotMk = $cpmk->mks()->first()->pivot->bobot ?? 0;
                                        $bobotCplCpmk = $cpmk->pivot->bobot ?? 0; // Ambil bobot dari cpmk_cpl
                                        $nilai = $nilaiCpmk ? $nilaiCpmk->nilai : 0;
                                        $kontribusi = ($bobotMk * $nilai / 100) * ($bobotCplCpmk / 100);
                                        ?>
                                        <tr>
                                            <td class="bg-light">{{ $cpmk->kode_cpmk }}</td>
                                            <td>{{ $cpmk->deskripsi }}</td>
                                            <td>{{ number_format($nilai, 2) }}</td>
                                            <td class="bobot-highlight">{{ $bobotMk }}%</td>
                                            <td class="bobot-highlight">{{ $bobotCplCpmk }}%</td>
                                            <td class="bg-light">{{ number_format($kontribusi * 100, 2) }}%</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    if (typeof jQuery === 'undefined') {
        console.error('jQuery tidak dimuat!');
    } else {
        $(document).ready(function() {
            // Tidak perlu form di sini, hanya tampilan
        });
    }
</script>
@endsection