@extends('layouts_adminlte.app')

@section('title', 'Penilaian CPL')

@section('css')
    <style>
        .card-header.bg-primary {
            background-color: #007bff !important;
            color: #fff;
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 600px;
            width: 90%;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .detail-table th,
        .detail-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        .detail-table th {
            background-color: #e9ecef;
            font-weight: bold;
            color: #007bff;
        }

        .detail-table .below-min {
            color: #dc3545;
            font-weight: bold;
        }

        .nested-table {
            width: 100%;
            border-collapse: collapse;
        }

        .nested-table th,
        .nested-table td {
            padding: 5px;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        .nested-table th {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        Penilaian CPL untuk {{ $mahasiswa->nama }} 
                        @if ($scope === 'periode')
                            (Periode: {{ $periode }}) - Semua Mata Kuliah
                        @else
                            (Seluruh Periode) - Semua Mata Kuliah
                        @endif
                    </h3>
                    <div>
                        @if ($scope === 'periode')
                            <a href="{{ route('penilaian.cpl.index', ['mahasiswa_id' => $mahasiswa->id, 'scope' => 'all']) }}"
                               class="btn btn-info btn-sm mr-2">
                                Lihat Seluruh Periode
                            </a>
                        @else
                            <a href="{{ route('penilaian.cpl.index', ['mahasiswa_id' => $mahasiswa->id, 'scope' => 'periode']) }}"
                               class="btn btn-info btn-sm mr-2">
                                Lihat Per Periode
                            </a>
                        @endif
                        <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ $periode }}&kelas={{ session('previous_kelas') }}"
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Mahasiswa
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="chart-container">
                        <canvas id="radarChart"></canvas>
                    </div>

                    <div class="mt-3">
                        <h4>Detail Pencapaian CPL Per Tahap Penilaian:</h4>
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Kode CPL</th>
                                    <th>Deskripsi CPL</th>
                                    <th>Pencapaian CPL Per Penilaian (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cplData as $cpl)
                                    @if (!empty($cpl['pencapaian_cpl_by_penilaian']))
                                        <tr>
                                            <td>{{ $cpl['kode_cpl'] }}</td>
                                            <td>{{ $cpl['deskripsi'] }}</td>
                                            <td>
                                                @foreach ($cpl['pencapaian_cpl_by_penilaian'] as $penilaianKe => $pencapaian)
                                                    Penilaian {{ $penilaianKe }}: {{ number_format($pencapaian, 2) }}%<br>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5">
                        <h4>Kontribusi MK dan CPMK terhadap CPL:</h4>
                        @foreach ($cplData as $cpl)
                            @if (!empty($cpl['contributions']))
                                <h5>{{ $cpl['kode_cpl'] }} - {{ $cpl['deskripsi'] }}</h5>
                                <table class="detail-table">
                                    <thead>
                                        <tr>
                                            <th>Kode MK</th>
                                            <th>Nama MK</th>
                                            <th>Kode CPMK</th>
                                            <th>Deskripsi CPMK</th>
                                            <th>Detail Penilaian</th>
                                            <th>Nilai Akhir CPMK</th>
                                            <th>Bobot MK (%)</th>
                                            <th>Kontribusi Skor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cpl['contributions'] as $contribution)
                                            <tr>
                                                <td>{{ $contribution['mk_kode'] }}</td>
                                                <td>{{ $contribution['mk_deskripsi'] }}</td>
                                                <td>{{ $contribution['cpmk_kode'] }}</td>
                                                <td>{{ $contribution['cpmk_deskripsi'] }}</td>
                                                <td>
                                                    <table class="nested-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Penilaian Ke</th>
                                                                <th>Nilai</th>
                                                                <th>Bobot (%)</th>
                                                                <th>Kontribusi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($contribution['detail_penilaian'] as $detail)
                                                                <tr>
                                                                    <td>{{ $detail['penilaian_ke'] }}</td>
                                                                    <td>{{ number_format($detail['nilai'], 2) }}</td>
                                                                    <td>{{ number_format($detail['bobot'], 2) }}</td>
                                                                    <td>{{ number_format($detail['kontribusi'], 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td>{{ number_format($contribution['nilai_akhir'], 2) }}</td>
                                                <td>{{ number_format($contribution['bobot'], 2) }}</td>
                                                <td>{{ number_format($contribution['kontribusi'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-3 text-right">
                        <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ $periode }}&kelas={{ session('previous_kelas') }}"
                           class="btn btn-primary">Pilih Mahasiswa Lain</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            const ctx = document.getElementById('radarChart').getContext('2d');

            console.log('Labels:', @json($labels));
            console.log('Datasets:', @json($datasets));

            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: @json($labels),
                    datasets: @json($datasets)
                },
                options: {
                    scales: {
                        r: {
                            suggestedMin: 0,
                            suggestedMax: 100,
                            ticks: {
                                stepSize: 20,
                                callback: function(value) {
                                    return value;
                                }
                            },
                            pointLabels: {
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
@endsection