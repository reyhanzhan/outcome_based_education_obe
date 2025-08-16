@extends('layouts_adminlte.app')

@section('title', 'Grafik CPL')

@section('css')
    <style>
        .card-header.bg-primary {
            background-color: #007bff !important;
            color: #fff;
        }

        .chart-container {
            position: relative;
            margin: 20px auto;
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px;
            height: 60vh;
            min-height: 300px;
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

        /* Styling untuk form select */
        .card-header .form-inline {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 400px; /* Batas maksimum form */
        }

        .card-header .form-group {
            flex: 1;
            margin: 0;
        }

        .card-header .form-control {
            width: 100%;
            min-width: 150px; /* Minimum lebar agar tetap fungsional */
            max-width: 100%; /* Mengikuti lebar form-inline */
            overflow: hidden; /* Mencegah teks meluber */
            text-overflow: ellipsis; /* Tambahkan ellipsis jika teks terlalu panjang */
            white-space: nowrap; /* Pastikan teks tidak pindah baris */
        }

        .card-header .select2-container .select2-selection--single {
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .card-header .select2-container .select2-dropdown {
            width: 100% !important;
            max-width: 400px !important; /* Batas maksimum dropdown */
            min-width: 150px !important; /* Minimum lebar dropdown */
        }

        /* Responsivitas untuk card header */
        @media (max-width: 991px) {
            .chart-container {
                height: 50vh;
                padding: 10px;
            }
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .card-header .form-inline {
                max-width: 300px;
                margin-top: 10px;
            }
            .detail-table th,
            .detail-table td {
                padding: 8px;
            }
        }

        @media (max-width: 768px) {
            .chart-container {
                height: 40vh;
                width: 95%;
                padding: 8px;
            }
            .card-header .form-inline {
                max-width: 250px;
            }
            .detail-table th,
            .detail-table td {
                padding: 6px;
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .chart-container {
                height: 35vh;
                width: 100%;
                margin: 10px 0;
            }
            .card-header {
                padding: 10px;
            }
            .card-header .form-inline {
                max-width: 100%;
                flex-direction: column;
                align-items: stretch;
            }
            .card-header .form-group {
                margin-bottom: 5px;
            }
            .card-header .form-control {
                width: 100%;
            }
            .detail-table th,
            .detail-table td {
                padding: 4px;
                font-size: 12px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex flex-column flex-md-row justify-content-between align-items-center p-3">
                    <h3 class="card-title mb-2 mb-md-0" style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 70%;">Grafik Pencapaian CPL untuk {{ $mahasiswa->nama ?? 'N/A' }} (Semua Periode)</h3>
                    <div class="form-inline">
                        <div class="form-group">
                            <label for="mahasiswa_id" class="mr-2">Pilih Mahasiswa:</label>
                            <select name="mahasiswa_id" id="mahasiswa_id" class="form-control">
                                @foreach ($mahasiswas as $mhs)
                                    <option value="{{ $mhs->id }}" {{ $mhs->id == ($mahasiswa->id ?? null) ? 'selected' : '' }}>
                                        {{ $mhs->nama }} ({{ $mhs->nim }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
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
                            <div class="table-responsive">
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
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Modal untuk Detail CPL -->
    <div class="modal fade" id="cplDetailModal" tabindex="-1" role="dialog" aria-labelledby="cplDetailModalLabel"
        aria-hidden="true">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 pada dropdown mahasiswa tanpa tombol clear
            $('#mahasiswa_id').select2({
                placeholder: "Pilih Mahasiswa",
                width: '100%',
                dropdownCssClass: 'custom-select2-dropdown',
                dropdownAutoWidth: true,
                minimumResultsForSearch: 3 // Menampilkan pencarian jika ada lebih dari 3 opsi
            });

            // Submit form saat ada perubahan seleksi
            $('#mahasiswa_id').on('change', function() {
                $(this).closest('form').submit();
            });

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
                                    return context.raw + ' %';
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
                                size: 14
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
                    const uniqueContributions = [];

                    console.log('Contributions:', cpl.contributions);

                    if (cpl.contributions && Array.isArray(cpl.contributions)) {
                        cpl.contributions.forEach(contribution => {
                            const key = `${contribution.mk_kode}-${contribution.cpmk_kode}-${contribution.nilai}`;
                            if (!uniqueContributions[key]) {
                                totalBobot += parseFloat(contribution.bobot) || 0;
                                totalKontribusi += parseFloat(contribution.kontribusi) || 0;
                                uniqueContributions[key] = true;
                                tbody.append(`
                                    <tr>
                                        <td>${contribution.mk_kode || 'N/A'} - ${contribution.mk_deskripsi || 'N/A'}</td>
                                        <td>${contribution.cpmk_kode || 'N/A'}</td>
                                        <td>${contribution.nilai ? parseFloat(contribution.nilai).toFixed(2) : '0.00'}</td>
                                        <td>${contribution.bobot ? parseFloat(contribution.bobot).toFixed(2) : '0.00'}</td>
                                        <td>${contribution.kontribusi ? parseFloat(contribution.kontribusi).toFixed(2) : '0.00'}</td>
                                    </tr>
                                `);
                            }
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