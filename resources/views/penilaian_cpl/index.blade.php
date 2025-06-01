<!-- resources/views/penilaian_cpl/index.blade.php -->
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
        height: 400px;
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
        padding: 12px;
        border: 1px solid #dee2e6;
        text-align: center;
    }

    .detail-table th {
        background-color: #e9ecef;
        font-weight: 600;
        color: #007bff;
    }

    .detail-table .below-min {
        color: #dc3545;
        font-weight: bold;
    }

    .modal-body table {
        width: 100%;
        border-collapse: collapse;
    }

    .modal-body th,
    .modal-body td {
        padding: 8px;
        border: 1px solid #dee2e6;
    }

    .modal-body th {
        background-color: #f8f9fa;
    }

    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
    }

    @media (max-width: 768px) {
        .chart-container {
            height: 300px;
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
                    Penilaian CPL untuk {{ $mahasiswa->nama }}
                    @if ($scope === 'periode')
                        (Periode: {{ $periode }})
                    @else
                        (Semua Periode hingga {{ $periode }})
                    @endif
                </h3>
                <div>
                    @if ($scope === 'periode')
                        <a href="{{ route('penilaian.cpl.index', ['mahasiswa_id' => $mahasiswa->id, 'scope' => 'all']) }}"
                           class="btn btn-info btn-sm mr-2">Lihat Semua Periode</a>
                    @else
                        <a href="{{ route('penilaian.cpl.index', ['mahasiswa_id' => $mahasiswa->id, 'scope' => 'periode']) }}"
                           class="btn btn-info btn-sm mr-2">Lihat Per Periode</a>
                    @endif
                    <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ $periode }}&kelas={{ session('previous_kelas') }}"
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($scope === 'all' && !empty($periodeList))
                    <div class="alert alert-info">
                        Menampilkan data dari periode: {{ implode(', ', $periodeList) }}
                    </div>
                @endif

                @if (empty($cplData))
                    <div class="alert alert-warning">Tidak ada data CPL untuk mahasiswa ini.</div>
                @else
                    <div class="chart-container">
                        <h4 class="text-center mb-3">Grafik Pencapaian CPL</h4>
                        <canvas id="radarChart"></canvas>
                    </div>

                    <div class="mt-4">
                        <h4>Ringkasan Pencapaian CPL</h4>
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Kode CPL</th>
                                    <th>Deskripsi CPL</th>
                                    <th>Pencapaian (%)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cplData as $index => $cpl)
                                    <tr>
                                        <td>{{ $cpl['kode_cpl'] }}</td>
                                        <td>{{ $cpl['deskripsi'] }}</td>
                                        <td class="{{ $cpl['pencapaian_cpl'] < $minStandard ? 'below-min' : '' }}">
                                            {{ number_format($cpl['pencapaian_cpl'], 2) }}%
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm detail-cpl-btn"
                                                    data-cpl-id="{{ $index }}"
                                                    data-cpl='{{ json_encode($cpl, JSON_HEX_QUOT | JSON_HEX_TAG) }}'>
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Modal untuk Detail CPL -->
<div class="modal fade" id="cplDetailModal" tabindex="-1" role="dialog" aria-labelledby="cplDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cplDetailModalLabel">Detail Pencapaian CPL</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 id="cpl-title"></h5>
                <p><strong>Deskripsi:</strong> <span id="cpl-deskripsi"></span></p>
                <p><strong>Pencapaian:</strong> <span id="cpl-pencapaian"></span>%</p>
                <h6>Mata Kuliah dan CPMK yang Mendukung</h6>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Kode CPMK</th>
                            <th>Nilai CPMK</th>
                            <th>Bobot (%)</th>
                            <th>Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody id="cpl-contributions"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th id="total-bobot"></th>
                            <th id="total-kontribusi"></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="mt-3">
                    <strong>Penjelasan Perhitungan:</strong>
                    <p>
                        Pencapaian CPL dihitung sebagai: (Total Kontribusi ÷ Total Bobot) × 100.
                        Setiap kontribusi dihitung sebagai (Nilai CPMK × Bobot MK) ÷ 100.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Ambil data labels dari PHP
        const labels = @json($labels);
        const datasets = @json($datasets);

        // Tentukan jenis grafik berdasarkan jumlah label
        const chartType = labels.length < 3 ? 'bar' : 'radar';

        // Inisialisasi grafik
        const ctx = document.getElementById('radarChart').getContext('2d');
        new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: datasets
            },
            options: chartType === 'radar' ? {
                scales: {
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
            } : {
                scales: {
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

        // Tangani klik tombol Detail secara manual
        $('.detail-cpl-btn').on('click', function() {
            try {
                const cplId = $(this).data('cpl-id');
                let cpl = $(this).data('cpl');

                console.log('CPL ID:', cplId);
                console.log('Raw CPL Data:', cpl);

                if (typeof cpl === 'string') {
                    try {
                        cpl = JSON.parse(cpl);
                    } catch (e) {
                        console.error('Gagal parse JSON:', e);
                        return;
                    }
                }

                console.log('Parsed CPL Data:', cpl);

                if (!cpl || !cpl.kode_cpl) {
                    console.error('Data CPL tidak valid atau kosong');
                    return;
                }

                $('#cplDetailModal').modal('show');

                $('#cpl-title').text(cpl.kode_cpl || 'N/A');
                $('#cpl-deskripsi').text(cpl.deskripsi || 'N/A');
                $('#cpl-pencapaian').text(cpl.pencapaian_cpl ? cpl.pencapaian_cpl.toFixed(2) : '0.00');

                const tbody = $('#cpl-contributions');
                tbody.empty();
                let totalBobot = 0;
                let totalKontribusi = 0;

                console.log('Contributions:', cpl.contributions);

                if (cpl.contributions && Array.isArray(cpl.contributions) && cpl.contributions.length > 0) {
                    cpl.contributions.forEach(contribution => {
                        totalBobot += parseFloat(contribution.bobot) || 0;
                        totalKontribusi += parseFloat(contribution.kontribusi) || 0;
                        tbody.append(`
                            <tr>
                                <td>${contribution.mk_kode || 'N/A'} - ${contribution.mk_deskripsi || 'N/A'}</td>
                                <td>${contribution.cpmk_kode || 'N/A'}</td>
                                <td>${contribution.nilai ? parseFloat(contribution.nilai).toFixed(2) : '0.00'}</td>
                                <td>${contribution.bobot ? parseFloat(contribution.bobot).toFixed(2) : '0.00'}</td>
                                <td>${contribution.kontribusi ? parseFloat(contribution.kontribusi).toFixed(2) : '0.00'}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`
                        <tr>
                            <td colspan="5">Tidak ada data kontribusi tersedia.</td>
                        </tr>
                    `);
                }

                $('#total-bobot').text(totalBobot.toFixed(2));
                $('#total-kontribusi').text(totalKontribusi.toFixed(2));
            } catch (error) {
                console.error('Error saat membuka modal:', error);
            }
        });

        $('.modal-footer .btn-secondary').on('click', function() {
            console.log('Tombol Tutup diklik');
            $('#cplDetailModal').modal('hide');
        });
        $('.modal-header .close').on('click', function() {
            console.log('Tombol X diklik');
            $('#cplDetailModal').modal('hide');
        });
    });
</script>
@endsection