@extends('layouts_adminlte.app')

@section('title', 'Input Nilai Mahasiswa')

@section('css')
    <style>
        .card-header.bg-primary {
            background-color: #007bff !important;
            color: #fff;
        }

        .table-bordered th,
        .table-bordered td {
            vertical-align: middle;
            text-align: center;
        }

        .bobot-highlight {
            background-color: #e9ecef;
            font-weight: bold;
            color: #000000;
        }

        .success-bg {
            background-color: #d4edda;
            color: #155724;
        }

        .error-bg {
            background-color: #f8d7da;
            color: #721c24;
        }

        .input-group .form-control {
            border-color: #007bff;
        }

        .input-group .form-control.below-min {
            border-color: #dc3545 !important;
            background-color: #fff3cd;
        }

        .min-standard {
            color: #6c757d;
            font-style: italic;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        .cpmk-code {
            color: #007bff;
            text-decoration: underline;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .cpmk-code:hover {
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .modal-body table {
            width: 100%;
        }

        @media (max-width: 576px) {

            .table th,
            .table td {
                font-size: 12px;
                padding: 5px;
            }

            .cpmk-code {
                font-size: 12px;
            }

            .modal-dialog {
                margin: 10px;
            }

            .modal-body {
                font-size: 14px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Input Nilai untuk {{ $mk->kode_mk }} - {{ $mk->deskripsi }}</h3>
                    <div class="btn-group">
                        <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ $periode }}&kelas={{ $kelasInput }}"
                            class="btn btn-secondary mr-5"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <a href="{{ route('penilaian.cpmk.index', ['mahasiswa_id' => $mahasiswa->id, 'mk_id' => $mk->id]) }}"
                            class="btn btn-info"><i class="fas fa-chart-line"></i> Ke Penilaian CPMK</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (!$hasValidBobot)
                        <div class="alert alert-warning">
                            Mohon isi bobot terlebih dahulu untuk CPMK terkait mata kuliah ini.
                        </div>
                    @endif

                    <form action="{{ route('nilai.mahasiswa.store') }}" method="POST" id="nilaiForm">
                        @csrf
                        <input type="hidden" name="nim" value="{{ $mahasiswa->nim }}">
                        <input type="hidden" name="mk_id" value="{{ $mk->id }}">

                        <!-- Form untuk mengatur standar minimum -->
                        <div class="form-group mb-3">
                            <label for="minStandard">Standar Minimum Nilai CPMK:</label>
                            <p class="form-control-static">{{ $minStandard }}</p>
                            <input type="hidden" name="min_standard" value="{{ $minStandard }}">
                            <small class="form-text text-muted">Standar minimum untuk semua CPMK di MK ini adalah
                                {{ $minStandard }}. Klik
                                kode CPMK untuk melihat deskripsi dan teknik penilaian.</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="bg-light" style="width: 15%;">Nama Mahasiswa</th>
                                        @foreach ($cpmks as $cpmk)
                                            <th class="bobot-highlight">
                                                <span class="cpmk-code" data-cpmk-id="{{ $cpmk->id }}"
                                                    data-content="{{ $cpmk->deskripsi ?? 'Deskripsi tidak tersedia' }}"
                                                    data-teknik="{{ json_encode(
                                                        $cpmk->teknikPenilaian->where('bobot', '>', 0)->map(function ($teknik) {
                                                                return ['teknik' => $teknik->teknik, 'bobot' => $teknik->bobot];
                                                            })->values()->toArray(),
                                                    ) }}"
                                                    style="cursor: pointer;">
                                                    {{ $cpmk->kode_cpmk }}
                                                    ({{ $cpmk->teknikPenilaian->sum('bobot') }}%)
                                                    <!-- Gunakan bobot dari teknikPenilaian -->
                                                </span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="bg-light">{{ $mahasiswa->nama }}</td>
                                        @foreach ($cpmks as $cpmk)
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="nilai[{{ $cpmk->id }}]"
                                                        class="form-control nilai-input" data-min="{{ $minStandard }}"
                                                        data-bobot="{{ $cpmk->teknikPenilaian->sum('bobot') }}" 
                                                    value="{{ number_format($nilaiCpmks[$cpmk->id] ?? 0, 2) }}"
                                                    min="0" max="100" step="0.01"
                                                    @if ($cpmk->teknikPenilaian->sum('bobot') == 0)
                                                        disabled
                                                    @endif> <!-- Nonaktifkan jika bobot 0 -->
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="submit" class="btn btn-primary" id="submitButton"
                                @if (!$hasValidBobot) disabled @endif>Simpan Nilai</button>
                        </div>
                    </form>

                    <!-- Modal untuk menampilkan deskripsi CPMK dan teknik penilaian -->
                    <div class="modal fade" id="cpmkDescriptionModal" tabindex="-1" role="dialog"
                        aria-labelledby="cpmkDescriptionModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="cpmkDescriptionModalLabel">Deskripsi CPMK</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body" id="cpmkDescriptionModalBody">
                                    <p><strong>Deskripsi:</strong> <span id="cpmkDescription"></span></p>
                                    <p><strong>Teknik Penilaian:</strong></p>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Teknik</th>
                                                <th>Bobot (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cpmkTeknikPenilaian"></tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Tampilkan modal saat mengklik kode CPMK
            $('.cpmk-code').on('click', function() {
                const cpmkId = $(this).data('cpmk-id');
                const description = $(this).data('content');
                const teknikPenilaian = $(this).data('teknik');

                $('#cpmkDescriptionModalLabel').text('Deskripsi ' + $(this).text().split(' (')[0]);
                $('#cpmkDescription').text(description);

                // Kosongkan tabel teknik penilaian
                $('#cpmkTeknikPenilaian').empty();

                // Tambahkan baris untuk setiap teknik penilaian
                if (teknikPenilaian && teknikPenilaian.length > 0) {
                    teknikPenilaian.forEach(function(teknik) {
                        $('#cpmkTeknikPenilaian').append(
                            '<tr>' +
                            '<td>' + teknik.teknik + '</td>' +
                            '<td>' + teknik.bobot + '</td>' +
                            '</tr>'
                        );
                    });
                } else {
                    $('#cpmkTeknikPenilaian').append(
                        '<tr><td colspan="2">Tidak ada teknik penilaian yang ditentukan.</td></tr>'
                    );
                }

                $('#cpmkDescriptionModal').modal('show');
            });

            $('.modal .close, .modal .btn-secondary').on('click', function() {
                $('#cpmkDescriptionModal').modal('hide');
            });

            // Validasi dan debugging submit
            $('#nilaiForm').on('submit', function(e) {
                console.log('Form submitted with data:', $(this).serialize()); // Debugging
                let isValid = true;
                $(this).find('.nilai-input').each(function() {
                    let value = parseFloat($(this).val()) || 0;
                    let bobot = parseInt($(this).data('bobot'));
                    if (bobot > 0 && (isNaN(value) || value < 0 || value > 100)) {
                        isValid = false;
                        toastr.error(
                            'Semua nilai harus antara 0 dan 100 untuk CPMK dengan bobot > 0.');
                        return false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            $('#submitButton').on('click', function(e) {
                console.log('Submit button clicked'); // Debugging
                if ($(this).is(':disabled')) {
                    e.preventDefault();
                    toastr.warning('Mohon isi bobot terlebih dahulu sebelum menyimpan nilai.');
                }
            });
        });
    </script>
@endsection
