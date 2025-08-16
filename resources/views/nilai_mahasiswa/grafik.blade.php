@extends('layouts_adminlte.app')

@section('title', 'Grafik Visualisasi CPMK')

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
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

    @media (max-width: 768px) {
        .chart-container {
            height: 400px;
        }
    }
</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    Grafik Visualisasi CPMK {{ $mahasiswa->nama }} untuk {{ $mk->kode_mk }} - {{ $mk->deskripsi }}
                </h3>
                <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ session('previous_periode') }}&kelas={{ session('previous_kelas') }}"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="mb-3">
                    <h4>Nilai Total Mata Kuliah: {{ number_format($totalScore, 2) }}</h4>
                </div>

                @if ($cpmks->isEmpty())
                    <div class="alert alert-warning">
                        Tidak ada CPMK yang terkait dengan mata kuliah ini.
                    </div>
                @else
                    <div class="chart-container">
                        <canvas id="cpmkChart"></canvas>
                    </div>

                    <div class="mt-3">
                        <h4>Detail Nilai CPMK:</h4>
                        <div class="table-responsive">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Kode CPMK</th>
                                    <th class="text-center">Deskripsi</th>
                                    <th>Nilai Asli</th>
                                    <th>Bobot (%)</th>
                                    <th>Nilai Akhir (Setelah Bobot)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cpmks as $cpmk)
                                    @php
                                        $nilaiInput = $nilaiCpmks[$cpmk->id] ?? 0;
                                        $bobot = $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0;
                                        $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                                    @endphp
                                    <tr>
                                        <td>{{ $cpmk->kode_cpmk }}</td>
                                        <td>{{ $cpmk->deskripsi }}</td>
                                        <td class="{{ $nilaiInput < $minStandard ? 'below-min' : '' }}">
                                            {{ number_format($nilaiInput, 2) }}
                                        </td>
                                        <td>{{ $bobot }}</td>
                                        <td>{{ number_format($nilaiAkhir, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        const ctx = document.getElementById('cpmkChart').getContext('2d');
        const labels = @json($labels);
        const data = @json($data);
        const minStandard = @json($minStandard);

        // Debugging di console untuk memastikan data
        console.log('Labels:', labels);
        console.log('Data:', data);
        console.log('Min Standard:', minStandard);

        // Tentukan tipe grafik berdasarkan jumlah CPMK
        const chartType = labels.length < 3 ? 'bar' : 'radar';

        new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nilai CPMK {{ $mahasiswa->nama }}',
                        data: data,
                        fill: chartType === 'radar' ? true : false,
                        backgroundColor: chartType === 'radar' ? 'rgba(0, 123, 255, 0.2)' : 'rgba(0, 123, 255, 0.5)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 2,
                        pointRadius: chartType === 'radar' ? 0 : 5,
                        pointHoverRadius: chartType === 'radar' ? 0 : 7
                    },
                    {
                        label: 'Standar Minimum',
                        data: Array(labels.length).fill(minStandard),
                        fill: chartType === 'radar' ? true : false,
                        backgroundColor: chartType === 'radar' ? 'rgba(255, 99, 132, 0.2)' : 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(258, 99, 132, 1)',
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }
                ]
            },
            options: {
                scales: chartType === 'bar' ? {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                } : {
                    r: {
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) {
                                return value + '%';
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