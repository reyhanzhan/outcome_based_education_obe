@extends('layouts_adminlte.app')

@section('title', 'Pilih Periode & Kelas')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pilih Periode & Kelas untuk Penilaian CPL</h3>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (!$periodes || $periodes->isEmpty())
                        <div class="alert alert-warning">
                            Tidak ada periode tersedia. Periksa data di tabel KRS.
                        </div>
                    @endif
                    <form id="filterForm" method="GET" action="{{ route('penilaian.cpl.choose_periode_dan_kelas') }}">
                        <div class="row">
                            <!-- Pilih Periode -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periode">1. Pilih Periode:</label>
                                    <select name="periode" id="periode" class="form-control" required>
                                        <option value="">-- Pilih Periode --</option>
                                        @foreach ($periodes as $periodeOption)
                                            <option value="{{ $periodeOption }}"
                                                {{ old('periode', request('periode')) == $periodeOption ? 'selected' : '' }}>
                                                {{ $periodeOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Pilih Kelas -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kelas">2. Pilih Kelas:</label>
                                    <select name="kelas" id="kelas" class="form-control">
                                        <option value="">-- Pilih Kelas --</option>
                                        @if ($periode)
                                            @foreach ($kelasOptions as $kelasOption)
                                                <option value="{{ $kelasOption->id }}"
                                                    {{ old('kelas', request('kelas')) == $kelasOption->id ? 'selected' : '' }}>
                                                    {{ $kelasOption->text }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Tidak ada kelas tersedia</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Menampilkan peringatan jika tidak ada mahasiswa -->
            @if (isset($mahasiswas) && $mahasiswas->isEmpty())
                <div class="alert alert-warning mt-3">
                    <strong>Peringatan!</strong> Tidak ada mahasiswa yang terdaftar pada kelas dan periode ini.
                </div>
            @endif

            <!-- Menampilkan Daftar Mahasiswa jika ada -->
            @if (isset($mahasiswas) && !$mahasiswas->isEmpty())
                <div class="card mt-3">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title">Daftar Mahasiswa Kelas: {{ $selectedKelas->kode_mk }} - {{ $namaMk }} (Periode: {{ $periode }})</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="mahasiswaTable" class="table table-bordered table-hover">
                                <thead style="text-align: center;">
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Kode MK</th>
                                        <th>Nama MK</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mahasiswas as $mhs)
                                        <tr>
                                            <td>{{ $mhs->nim ?? 'N/A' }}</td>
                                            <td>{{ $mhs->nama ?? 'N/A' }}</td>
                                            <td>{{ $selectedKelas->kode_mk }}</td>
                                            <td>{{ $namaMk }}</td>
                                            <td>
                                                <div class="action-buttons">
                                                    @if ($mhs->id)
                                                        <a href="{{ route('penilaian.cpl.index', $mhs->id) }}"
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-chart-bar"></i> Penilaian CPL
                                                        </a>
                                                    @else
                                                        <button class="btn btn-info btn-sm" disabled>
                                                            <i class="fas fa-chart-bar"></i> Penilaian CPL (Data Tidak Lengkap)
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- JS DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk periode
            $('#periode').select2({
                placeholder: "-- Pilih Periode --",
                width: '100%',
                dropdownCssClass: 'custom-select2-dropdown',
                dropdownAutoWidth: true,
                minimumResultsForSearch: 3
            });

            // Inisialisasi Select2 untuk kelas
            $('#kelas').select2({
                placeholder: "-- Pilih Kelas --",
                width: '100%',
                dropdownCssClass: 'custom-select2-dropdown',
                dropdownAutoWidth: true,
                minimumResultsForSearch: 3
            });

            // Fungsi untuk memuat opsi kelas berdasarkan periode
            function loadKelasOptions(periode) {
                if (periode) {
                    $.ajax({
                        url: '{{ route('penilaian.cpl.get_kelas_by_periode') }}',
                        method: 'GET',
                        data: { periode: periode },
                        success: function(response) {
                            let kelasSelect = $('#kelas');
                            let currentValue = kelasSelect.val();
                            kelasSelect.empty();
                            kelasSelect.append(new Option('-- Pilih Kelas --', '', true, true));

                            response.options.forEach(function(option) {
                                let newOption = new Option(option.text, option.id, false, false);
                                kelasSelect.append(newOption);
                                if (option.id === currentValue) {
                                    newOption.selected = true;
                                }
                            });
                            kelasSelect.trigger('change');
                        },
                        error: function(xhr) {
                            console.log('Error fetching kelas: ', xhr);
                        }
                    });
                } else {
                    $('#kelas').empty().append(new Option('-- Pilih Kelas --', '', true, true)).trigger('change');
                }
            }

            // Muat opsi kelas saat periode dipilih
            $('#periode').on('select2:select', function(e) {
                let periode = $(this).val();
                console.log('Selected Periode: ' + periode);
                loadKelasOptions(periode);
                $('#kelas').val(null).trigger('change'); // Reset kelas saat periode berubah
            });

            // Submit form saat kelas dipilih
            $('#kelas').on('select2:select', function(e) {
                console.log('Selected Kelas: ' + $(this).val());
                $('#filterForm').submit();
            });

            // Muat ulang opsi kelas saat halaman dimuat
            let initialPeriode = $('#periode').val();
            if (initialPeriode) {
                loadKelasOptions(initialPeriode);
            }

            // Inisialisasi DataTable
            $('#mahasiswaTable').DataTable({
                paging: true,
                pageLength: 10,
                searching: true,
                responsive: true,
                order: [[0, 'asc']],
                language: {
                    search: "Cari Nama Mahasiswa:",
                    paginate: {
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri",
                    infoEmpty: "Tidak ada data",
                    lengthMenu: "Tampilkan _MENU_ entri"
                }
            });

            // Debugging saat submit
            $('#filterForm').on('submit', function() {
                let periode = $('#periode').val();
                let kelas = $('#kelas').val();
                console.log('Form submitted with Periode: ' + periode + ', Kelas: ' + kelas);
            });
        });
    </script>

    <!-- CSS DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .form-group .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #d2d6de;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            top: 0 !important;
            right: 10px !important;
            display: flex !important;
            align-items: center !important;
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table th,
            .table td {
                font-size: 12px !important;
                padding: 8px !important;
            }
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            justify-content: center;
        }

        .action-buttons .btn {
            min-width: 120px;
            text-align: center;
            padding: 5px 10px;
            font-size: 12px;
        }

        @media (max-width: 576px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .action-buttons .btn {
                width: 100%;
                max-width: 200px;
                margin-bottom: 5px;
            }
        }
    </style>
@endsection