@extends('layouts_adminlte.app')

@section('title', 'Grafik Radar Penilaian CPMK')

@section('css')
    <style>
        .card-header.bg-primary {
            background-color: #007bff !important;
            color: #fff;
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 600px; /* Ukuran grafik diperbesar */
            width: 90%; /* Lebar grafik diperbesar */
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .detail-table th, .detail-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .detail-table th {
            background-color: #e9ecef; /* Abu-abu muda untuk header */
            font-weight: bold;
            color: #007bff; /* Biru untuk kontras */
        }

        .detail-table .below-min {
            color: #dc3545; /* Merah untuk nilai di bawah standar minimum */
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
                    <h3 class="card-title">Grafik Radar Penilaian CPMK {{ $mahasiswa->nama }} untuk {{ $mk->kode_mk }} - {{ $mk->deskripsi }}</h3>
                    <a href="{{ route('penilaian.cpmk.choose_mahasiswa') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Pilih Mahasiswa</a>
                </div>
                <div class="card-body">
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
                                    <th>Nilai CPMK Mahasiswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($labels as $index => $label)
                                    @php
                                        $nilaiInput = $mahasiswa->nilaiCpmks()->where('mk_id', $mk->id)->where('cpmk_id', $cpmks[$index]->id)->first()->nilai ?? 0;
                                        $bobot = $cpmks[$index]->mks()->where('mk_id', $mk->id)->first()->pivot->bobot ?? 0;
                                        $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                                        $minStandard = $minStandard ?? 55;
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ $cpmks[$index]->deskripsi }}</td>
                                        <td class="{{ $nilaiInput < $minStandard ? 'below-min' : '' }}">
                                            {{ number_format($nilaiAkhir, 0) }}
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if (typeof jQuery === 'undefined') {
            console.error('jQuery tidak dimuat!');
        } else {
            $(document).ready(function() {
                const ctx = document.getElementById('radarChart').getContext('2d');
                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: @json($labels), // Hanya kode CPMK, misalnya "CPMK011"
                        datasets: [
                            {
                                label: 'Nilai CPMK {{ $mahasiswa->nama }}',
                                data: @json($data), // Nilai akhir setelah bobot
                                fill: true,
                                backgroundColor: 'rgba(0, 123, 255, 0.2)', // Biru transparan
                                borderColor: 'rgba(0, 123, 255, 1)', // Biru solid
                                pointBackgroundColor: function(context) {
                                    const value = context.raw; // Nilai akhir setelah bobot
                                    const min = @json($minStandard ?? 55);
                                    const nilaiInput = @json($mahasiswa->nilaiCpmks()->where('mk_id', $mk->id)->pluck('nilai', 'cpmk_id')->toArray())[[context.dataIndex]] ?? 0; // Ambil nilai input asli
                                    return nilaiInput < min ? 'rgba(255, 99, 132, 1)' : 'rgba(0, 123, 255, 1)';
                                },
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: function(context) {
                                    const value = context.raw; // Nilai akhir setelah bobot
                                    const min = @json($minStandard ?? 55);
                                    const nilaiInput = @json($mahasiswa->nilaiCpmks()->where('mk_id', $mk->id)->pluck('nilai', 'cpmk_id')->toArray())[[context.dataIndex]] ?? 0; // Ambil nilai input asli
                                    return nilaiInput < min ? 'rgba(255, 99, 132, 1)' : 'rgba(0, 123, 255, 1)';
                                }
                            },
                            {
                                label: 'Standar Minimum',
                                data: @json(array_fill(0, count($labels), $minStandard ?? 55)), // Standar minimum, misalnya 55
                                fill: true,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)', // Merah transparan
                                borderColor: 'rgba(255, 99, 132, 1)', // Merah solid
                                pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                                pointBorderColor: '#fff',
                                pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
                            }
                        ]
                    },
                    options: {
                        scales: {
                            r: {
                                suggestedMin: 0,
                                suggestedMax: 100, // Sesuaikan dengan skala maksimum (0-100 untuk nilai CPMK)
                                ticks: {
                                    stepSize: 20,
                                    callback: function(value) { return value; }
                                },
                                pointLabels: {
                                    font: {
                                        size: 12
                                    }
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
        }
    </script>
@endsection