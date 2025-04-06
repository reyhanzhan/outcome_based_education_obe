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

        /* Pastikan tab terlihat interaktif */
        .nav-tabs .nav-link {
            cursor: pointer;
        }

        .nav-tabs .nav-link:hover {
            background-color: #f8f9fa;
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
                    <!-- Tab Navigasi -->
                    <ul class="nav nav-tabs" id="penilaianTab" role="tablist">
                        @for ($i = 1; $i <= $jumlahPenilaian; $i++)
                            <li class="nav-item">
                                <a class="nav-link {{ $i == 1 ? 'active' : '' }}" id="penilaian-{{ $i }}-tab" data-toggle="tab" href="#penilaian-{{ $i }}" role="tab" aria-controls="penilaian-{{ $i }}" aria-selected="{{ $i == 1 ? 'true' : 'false' }}">
                                    Penilaian {{ $i }}
                                </a>
                            </li>
                        @endfor
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="penilaianTabContent">
                        @for ($i = 1; $i <= $jumlahPenilaian; $i++)
                            <div class="tab-pane fade {{ $i == 1 ? 'show active' : '' }}" id="penilaian-{{ $i }}" role="tabpanel" aria-labelledby="penilaian-{{ $i }}-tab">
                                <div class="mb-3">
                                    <h4>Nilai Total MK: {{ number_format($finalScores[$i], 2) }}</h4>
                                </div>

                                <div class="chart-container">
                                    <canvas id="radarChart-{{ $i }}"></canvas>
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
                                            @foreach ($radarDataPerPenilaian[$i]['labels'] as $index => $label)
                                                @php
                                                    $cpmk = $cpmks[$index];
                                                    $nilaiInput = $nilai[$i][$cpmk->id] ?? 0;
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
                        @endfor
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
            // Inisialisasi tab secara manual (sebagai fallback)
            $('#penilaianTab a').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });

            // Inisialisasi grafik radar untuk setiap tab
            @for ($i = 1; $i <= $jumlahPenilaian; $i++)
                const ctx{{ $i }} = document.getElementById('radarChart-{{ $i }}').getContext('2d');
                const nilaiInputs{{ $i }} = @json($nilai[$i]);

                // Debugging
                console.log('Labels for Penilaian {{ $i }}:', @json($radarDataPerPenilaian[$i]['labels']));
                console.log('Data for Penilaian {{ $i }}:', @json($radarDataPerPenilaian[$i]['data']));
                console.log('Nilai Inputs for Penilaian {{ $i }}:', nilaiInputs{{ $i }});

                new Chart(ctx{{ $i }}, {
                    type: 'radar',
                    data: {
                        labels: @json($radarDataPerPenilaian[$i]['labels']),
                        datasets: [
                            {
                                label: 'Nilai CPMK {{ $mahasiswa->nama }} (Penilaian {{ $i }})',
                                data: @json($radarDataPerPenilaian[$i]['data']),
                                fill: true,
                                backgroundColor: 'rgba(0, 123, 255, 0.2)', // Biru transparan
                                borderColor: 'rgba(0, 123, 255, 1)', // Biru solid
                                borderWidth: 2,
                                pointRadius: 0, // Menghilangkan titik
                                pointHoverRadius: 0 // Menghilangkan titik saat hover
                            },
                            {
                                label: 'Standar Minimum',
                                data: @json(array_fill(0, count($radarDataPerPenilaian[$i]['labels']), $minStandard ?? 55)),
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
            @endfor
        });
    </script>
@endsection