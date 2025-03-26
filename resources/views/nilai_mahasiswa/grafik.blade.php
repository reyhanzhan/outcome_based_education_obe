@extends('layouts_adminlte.app')

@section('title', 'Grafik Radar Visualisasi CPMK')

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
            text-align: left;
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

        .list-group-item {
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Grafik Radar Visualisasi CPMK {{ $mahasiswa->nama }} untuk {{ $mk->kode_mk }} - {{ $mk->deskripsi }}</h3>
                    <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ session('previous_periode') }}&kelas={{ session('previous_kelas') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h4>Nilai Total MK: {{ number_format($finalScore, 2) }}</h4>
                    </div>

                    <div class="chart-container">
                        <canvas id="radarChart"></canvas>
                    </div>

                    <div class="mt-3">
                        <h4>Detail Nilai CPMK:</h4>
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Kode CPMK</th>
                                    <th>Deskripsi</th>
                                    <th>Nilai Asli</th>
                                    <th>Bobot (%)</th>
                                    <th>Nilai Akhir (Setelah Bobot)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($labels as $index => $label)
                                    @php
                                        $cpmk = $cpmks[$index];
                                        $nilaiInput = $mahasiswa->nilaiCpmks()->where('mk_id', $mk->id)->where('cpmk_id', $cpmk->id)->first()->nilai ?? 0;
                                        $bobot = $cpmk->mks->isNotEmpty() ? ($cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0) : 0;
                                        $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                                        $minStandard = $minStandard ?? 55;
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ $cpmk->deskripsi }}</td>
                                        <td class="{{ $nilaiInput < $minStandard ? 'below-min' : '' }}">
                                            {{ number_format($nilaiInput, 0) }}
                                        </td>
                                        <td>{{ $bobot }}</td>
                                        <td>{{ number_format($nilaiAkhir, 2) }}</td>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            const ctx = document.getElementById('radarChart').getContext('2d');
            const nilaiInputs = @json($mahasiswa->nilaiCpmks()->where('mk_id', $mk->id)->pluck('nilai', 'cpmk_id')->toArray());
            const cpmkIds = @json($cpmks->pluck('id')->toArray());

            // Debugging
            console.log('Labels:', @json($labels));
            console.log('Data:', @json($data));
            console.log('Nilai Inputs:', nilaiInputs);
            console.log('CPMK IDs:', cpmkIds);

            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: @json($labels),
                    datasets: [
                        {
                            label: 'Nilai CPMK {{ $mahasiswa->nama }}',
                            data: @json($data),
                            fill: true,
                            backgroundColor: 'rgba(0, 123, 255, 0.2)', // Biru transparan
                            borderColor: 'rgba(0, 123, 255, 1)', // Biru solid
                            borderWidth: 2,
                            pointRadius: 0, // Menghilangkan titik
                            pointHoverRadius: 0 // Menghilangkan titik saat hover
                        },
                        {
                            label: 'Standar Minimum',
                            data: @json(array_fill(0, count($labels), $minStandard ?? 55)),
                            fill: true,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)', // Merah transparan
                            borderColor: 'rgba(255, 99, 132, 1)', // Merah solid
                            borderWidth: 2,
                            pointRadius: 0, // Menghilangkan titik
                            pointHoverRadius: 0 // Menghilangkan titik saat hover
                        }
                    ]
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
                                color: 'rgba(0, 0, 0, 0.1)' // Warna garis grid
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
                                    return context.label + ': ' + context.raw;
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