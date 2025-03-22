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
    </style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Penilaian CPMK untuk {{ $mk->kode_mk }} - {{ $mk->deskripsi }}</h3>
                    <a href="{{ route('penilaian.cpmk.choose_mk', $mahasiswa_id) }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Pilih Mata Kuliah</a>
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
                                    <th class="bg-light" style="width: 15%;">Nama Mahasiswa</th>
                                    @foreach ($cpmks as $cpmk)
                                        <th class="bobot-highlight">{{ $cpmk->kode_cpmk }} (Bobot: {{ $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0 }}%)</th>
                                    @endforeach
                                    <th class="bg-light">Nilai Total MK</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mahasiswas as $mahasiswa)
                                    <tr>
                                        <td class="bg-light">{{ $mahasiswa->nama }}</td>
                                        @foreach ($cpmks as $cpmk)
                                            <td>
                                                @php
                                                    // Ambil nilai dari relasi yang sudah dimuat
                                                    $nilaiCpmk = $cpmk->nilaiCpmks->where('mahasiswa_id', $mahasiswa->id)->first();
                                                    $nilaiInput = $nilaiCpmk ? $nilaiCpmk->nilai : 0;
                                                    $bobot = $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0;
                                                    $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                                                    $minStandard = $minStandard ?? 55;
                                                @endphp
                                                <span class="{{ $nilaiInput < $minStandard ? 'below-min' : '' }}">
                                                    {{ number_format($nilaiAkhir, 0) }}
                                                </span>
                                            </td>
                                        @endforeach
                                        <td class="bg-light">
                                            {{ number_format(app('App\Http\Controllers\PenilaianCpmkController')->calculateMkScore($mk->id, $mahasiswa->id), 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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