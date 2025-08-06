@extends('layouts_adminlte.app')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    Detail Evaluasi OBE untuk {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }}) pada {{ $mk->kode_mk }} - {{ $mk->deskripsi }}
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Periode:</strong> {{ $evaluation->periode }} <br>
                    <strong>Tahun Kurikulum:</strong> {{ $evaluation->tahun }} <br>
                    <strong>Total Score:</strong> {{ number_format($evaluation->total_score, 0) }} <br>
                    <strong>Tanggal Evaluasi:</strong> {{ $evaluation->created_at }}
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
                                    <th>Kode CPMK</th>
                                    <th>Deskripsi CPMK</th>
                                    <th>Bobot (%)</th>
                                    <th>Standar Minimum</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cpmks as $cpmk)
                                    @php
                                        $nilaiInput = $nilaiCpmks[$cpmk->id] ?? 0;
                                        $pivot = $cpmk->mks->where('id', $mk->id)->first()->pivot ?? (object) ['bobot' => 0, 'min_standard' => 55];
                                        $bobot = $pivot->bobot ?? 0;
                                        $minStandard = $pivot->min_standard ?? 55;
                                        $isBelowMinimum = $nilaiInput < $minStandard;
                                    @endphp
                                    <tr>
                                        <td>{{ $cpmk->kode_cpmk }}</td>
                                        <td>{{ $cpmk->deskripsi ?? 'Tidak ada deskripsi' }}</td>
                                        <td>{{ $bobot }}%</td>
                                        <td>{{ $minStandard }}</td>
                                        <td style="color: {{ $isBelowMinimum ? 'red' : 'inherit' }}">{{ number_format($nilaiInput, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <a href="{{ route('evaluasi.obe.history', ['mahasiswa_id' => $mahasiswa->id, 'mk_id' => $mk->id]) }}"
                   class="btn btn-secondary mt-3">
                    <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
                </a>
            </div>
        </div>
    </div>
</section>
@endsection