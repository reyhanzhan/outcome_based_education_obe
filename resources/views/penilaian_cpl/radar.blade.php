@extends('layouts_adminlte.app')

@section('title', 'Grafik Radar Penilaian CPL')

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
                    <h3 class="card-title">Grafik Radar Penilaian CPL {{ $mahasiswa->nama }} untuk {{ $mk->kode_mk }} - {{ $mk->deskripsi }}</h3>
                    <a href="{{ route('penilaian.cpl.choose_mk', $mahasiswa->id) }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Pilih Mata Kuliah</a>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="radarChart"></canvas>
                    </div>

                    <div class="mt-3">
                        <h4>Detail Nilai CPL:</h4>
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Kode CPL</th>
                                    <th>Deskripsi</th>
                                    <th>Nilai Mahasiswa (0-100%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($labels as $index => $label)
                                    @php
                                        $cplScore = $data[$index];
                                        $cpl = $cpls[$index];
                                        $minStandard = 55; // Standar minimum default, sesuaikan jika ada di session
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>{{ $cpl->deskripsi }}</td>
                                        <td class="{{ $cplScore < $minStandard ? 'below-min' : '' }}">
                                            {{ number_format($cplScore, 0) }}%
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
                        labels: @json($labels), // Kode CPL, misalnya "CPL06"
                        datasets: [
                            {
                                label: 'Nilai CPL {{ $mahasiswa->nama }}',
                                data: @json($data), // Nilai CPL mahasiswa (0-100%)
                                fill: true,
                                backgroundColor: 'rgba(0, 123, 255, 0.2)', // Biru transparan
                                borderColor: 'rgba(0, 123, 255, 1)', // Biru solid
                                pointBackgroundColor: function(context) {
                                    const value = context.raw;
                                    const min = @json($minStandard ?? 55);
                                    return value < min ? 'rgba(255, 99, 132, 1)' : 'rgba(0, 123, 255, 1)';
                                },
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: function(context) {
                                    const value = context.raw;
                                    const min = @json($minStandard ?? 55);
                                    return value < min ? 'rgba(255, 99, 132, 1)' : 'rgba(0, 123, 255, 1)';
                                }
                            }
                        ]
                    },
                    options: {
                        scales: {
                            r: {
                                suggestedMin: 0,
                                suggestedMax: 100, // Sesuaikan dengan skala maksimum (0-100 untuk nilai CPL)
                                ticks: {
                                    stepSize: 20,
                                    callback: function(value) { return value + '%'; }
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
                                        return context.label + ': ' + context.raw + '%';
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