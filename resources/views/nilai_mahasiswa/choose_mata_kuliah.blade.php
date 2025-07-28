@extends('layouts_adminlte.app')

@section('title', 'Pilih Periode & Kelas')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pilih Periode & Kelas</h3>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}">
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
                                                    {{ old('kelas', request('kelas')) == $kelasOption->id || (isset($selectedKelas) && $selectedKelas->kode_mk . '|' . $selectedKelas->nama_kelas == $kelasOption->id) ? 'selected' : '' }}>
                                                    {{ $kelasOption->text }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>No classes available</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Menampilkan peringatan jika tidak ada mahasiswa -->
            @if (isset($mahasiswas) && count($mahasiswas) === 0)
                <div class="alert alert-warning mt-3">
                    <strong>Peringatan!</strong> Tidak ada mahasiswa yang terdaftar pada kelas dan periode ini.
                </div>
            @endif

            <!-- Menampilkan Daftar Mahasiswa jika ada -->
            @if (isset($mahasiswas) && count($mahasiswas) > 0)
                <div class="card mt-3">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title">Daftar Mahasiswa Kelas: {{ $selectedKelas->kode_mk }} - {{ $namaMk }}
                            (Periode: {{ $periode }})</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="mahasiswaTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">NIM</th>
                                        <th class="text-center">Nama Mahasiswa</th>
                                        <th class="text-center">Kode MK</th>
                                        <th class="text-center">Nama MK</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mahasiswas as $mhs)
                                        @if ($mhs)
                                            <tr>
                                                <td>{{ $mhs->nim ?? 'N/A' }}</td>
                                                <td>{{ $mhs->nama ?? 'N/A' }}</td>
                                                <td>{{ $selectedKelas->kode_mk }}</td>
                                                <td>{{ $namaMk }}</td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <!-- Tombol Input Nilai -->
                                                        <a href="{{ route('nilai.mahasiswa.index', ['nim' => $mhs->nim ?? '', 'kode_mk' => $selectedKelas->kode_mk]) }}"
                                                            class="btn btn-primary btn-sm {{ request()->routeIs('nilai.mahasiswa.index') && (string) request()->segment(2) === (string) ($mhs->nim ?? '') && (string) request()->segment(4) === (string) $selectedKelas->kode_mk ? 'active' : '' }}">
                                                            <i class="fas fa-edit"></i> Input Nilai
                                                        </a>
                                                        <!-- Tombol Penilaian CPMK -->
                                                        @if (isset($mhs->id) && isset($selectedKelas->id))
                                                            <!-- Debugging -->
                                                            @if (request()->routeIs('penilaian.cpmk.index'))
                                                                <div style="color: red;">
                                                                    Debug CPMK: Segment 3 = {{ request()->segment(3) }},
                                                                    $mhs->id = {{ $mhs->id }}
                                                                    ({{ gettype($mhs->id) }}),
                                                                    Segment 4 = {{ request()->segment(4) }},
                                                                    $selectedKelas->id = {{ $selectedKelas->id }}
                                                                    ({{ gettype($selectedKelas->id) }})
                                                                </div>
                                                            @endif
                                                            <a href="{{ route('penilaian.cpmk.index', ['mahasiswa_id' => $mhs->id, 'mk_id' => $selectedKelas->id]) }}"
                                                                class="btn btn-info btn-sm {{ request()->routeIs('penilaian.cpmk.index') && (string) request()->segment(3) === (string) $mhs->id && (string) request()->segment(4) === (string) $selectedKelas->id ? 'active' : '' }}">
                                                                <i class="fas fa-chart-bar"></i> Penilaian CPMK
                                                            </a>
                                                            <!-- Tambahkan tombol Penilaian CPL -->
                                                            @if (request()->routeIs('penilaian.cpl.index'))
                                                                <div style="color: red;">
                                                                    Debug CPL: Segment 3 = {{ request()->segment(3) }},
                                                                    $mhs->id = {{ $mhs->id }}
                                                                    ({{ gettype($mhs->id) }})
                                                                </div>
                                                            @endif
                                                            <a href="{{ route('penilaian.cpl.index', $mhs->id) }}"
                                                                class="btn btn-warning btn-sm {{ request()->routeIs('penilaian.cpl.index') && (string) request()->segment(3) === (string) $mhs->id ? 'active' : '' }}">
                                                                <i class="fas fa-chart-pie"></i> Capaian Profil Lulusan
                                                            </a>
                                                            <!-- Tombol Grafik -->
                                                            @if (request()->routeIs('nilai.mahasiswa.grafik'))
                                                                <div style="color: red;">
                                                                    Debug Grafik: Segment 2 = {{ request()->segment(2) }},
                                                                    $mhs->nim = {{ $mhs->nim ?? '' }}
                                                                    ({{ gettype($mhs->nim) }}),
                                                                    Segment 4 = {{ request()->segment(4) }},
                                                                    $selectedKelas->kode_mk = {{ $selectedKelas->kode_mk }}
                                                                    ({{ gettype($selectedKelas->kode_mk) }})
                                                                </div>
                                                            @endif
                                                            <a href="{{ route('nilai.mahasiswa.grafik', ['nim' => $mhs->nim ?? '', 'kode_mk' => $selectedKelas->kode_mk]) }}"
                                                                class="btn btn-success btn-sm {{ request()->routeIs('nilai.mahasiswa.grafik') && (string) request()->segment(2) === (string) ($mhs->nim ?? '') && (string) request()->segment(4) === (string) $selectedKelas->kode_mk ? 'active' : '' }}">
                                                                <i class="fas fa-chart-line"></i> Grafik Cpmk
                                                            </a>
                                                        @else
                                                            <button class="btn btn-info btn-sm" disabled>
                                                                <i class="fas fa-chart-bar"></i> Penilaian CPMK (Data Tidak
                                                                Lengkap)
                                                            </button>

                                                            <button class="btn btn-success btn-sm" disabled>
                                                                <i class="fas fa-chart-line"></i> Grafik (Data Tidak
                                                                Lengkap)
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
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
                        url: '{{ route('get.kelas.by.periode') }}',
                        method: 'GET',
                        data: {
                            periode: periode
                        },
                        success: function(response) {
                            let kelasSelect = $('#kelas');
                            let currentValue = kelasSelect.val(); // Simpan nilai saat ini
                            kelasSelect.empty(); // Kosongkan dropdown
                            kelasSelect.append(new Option('-- Pilih Kelas --', '', true, true));

                            // Isi dropdown dengan data dari AJAX
                            response.options.forEach(function(option) {
                                let newOption = new Option(option.text, option.id, false,
                                    false);
                                kelasSelect.append(newOption);
                                if (option.id === currentValue) {
                                    newOption.selected = true;
                                }
                            });
                            kelasSelect.trigger('change'); // Perbarui Select2
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
            $(document).ready(function() {
                let periode = $('#periode').val();
                if (periode) {
                    loadKelasOptions(periode);
                }

                // Inisialisasi DataTable dengan pagination dan pencarian
                $('#mahasiswaTable').DataTable({
                    paging: true, // Aktifkan pagination
                    pageLength: 10, // Jumlah baris per halaman
                    searching: true, // Aktifkan pencarian
                    responsive: true, // Responsivitas
                    order: [
                        [0, 'asc']
                    ], // Urutkan berdasarkan kolom NIM (indeks 0)
                    language: {
                        search: "Cari Nama Mahasiswa:", // Ubah label pencarian
                        paginate: {
                            next: "Selanjutnya",
                            previous: "Sebelumnya"
                        },
                        info: "Menampilkan _START_ hingga _END_ dari _TOTAL_ entri", // Kustomisasi info
                        infoEmpty: "Tidak ada data", // Info saat kosong
                        lengthMenu: "Tampilkan _MENU_ entri" // Kustomisasi dropdown jumlah entri
                    }
                });
            });

            // Debugging saat submit
            $('#filterForm').on('submit', function() {
                let periode = $('#periode').val();
                console.log('Form submitted with Periode: ' + periode);
                console.log('Form submitted with Kelas: ' + $('#kelas').val());
                loadKelasOptions(periode); // Muat ulang opsi kelas saat submit
            });
        });
    </script>

    <!-- CSS DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .btn.active {
            background-color: #004680 !important;
            color: white !important;
            border-color: #004680 !important;
            box-shadow: none !important;
            /* Menghindari shadow default Bootstrap */
        }

        /* Hover effect untuk tombol aktif */
        .btn.active:hover {
            background-color: #003559 !important;
            color: white !important;
            border-color: #003559 !important;
        }

        .form-group .select2-container {
            width: 100% !important;
        }

        /* Atur tinggi dan posisi teks di dalam elemen input Select2 */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            /* Sesuaikan dengan tinggi input AdminLTE */
            border: 1px solid #d2d6de;
            /* Warna border sesuai tema AdminLTE */
            display: flex !important;
            /* Pastikan flex diterapkan */
            align-items: center !important;
            /* Memaksa posisi vertikal tengah */
        }

        /* Atur tombol dropdown agar sejajar */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            /* Sesuaikan dengan tinggi input */
            top: 0 !important;
            right: 10px !important;
            display: flex !important;
            align-items: center !important;
            /* Memastikan panah tetap di tengah */
        }

        /* Atur responsivitas tabel */
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

        /* Atur tata letak tombol menggunakan flexbox */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            /* Izinkan tombol membungkus ke baris berikutnya jika tidak cukup ruang */
            gap: 5px;
            /* Jarak antar tombol */
            justify-content: center;
            /* Pusatkan tombol secara horizontal */
        }

        /* Atur ukuran tombol */
        .action-buttons .btn {
            min-width: 120px;
            /* Lebar minimum tombol */
            text-align: center;
            padding: 5px 10px;
            /* Padding tombol */
            font-size: 12px;
            /* Ukuran font tombol */
        }

        /* Responsivitas untuk layar kecil */
        @media (max-width: 576px) {
            .action-buttons {
                flex-direction: column;
                /* Tumpuk tombol secara vertikal */
                align-items: center;
                /* Pusatkan tombol secara vertikal */
            }

            .action-buttons .btn {
                width: 100%;
                /* Tombol mengambil lebar penuh */
                max-width: 200px;
                /* Batasi lebar maksimum */
                margin-bottom: 5px;
                /* Jarak antar tombol saat ditumpuk */
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Pastikan jQuery dimuat
            if (typeof jQuery === 'undefined') {
                console.error('jQuery tidak dimuat!');
            } else {
                // Aktifkan pushmenu secara manual jika diperlukan
                $('[data-widget="pushmenu"]').on('click', function() {
                    $('body').toggleClass('sidebar-collapse');
                });
            }
        });
    </script>
@endsection
