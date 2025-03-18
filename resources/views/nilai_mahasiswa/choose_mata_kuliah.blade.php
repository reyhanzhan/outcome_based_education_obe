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

                        <!-- Tombol Tampilkan -->
                        {{-- <div class="text-right">
                            <button type="submit" class="btn btn-warning"><i class="fas fa-search"></i> Tampilkan</button>
                        </div> --}}
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
                        <h3 class="card-title">Daftar Mahasiswa Kelas: {{ $selectedKelas->kode_mk }} -
                            {{ $selectedKelas->nama_kelas }} (Periode: {{ $periode }})</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
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
                                        @if ($mhs)
                                            <!-- Pastikan $mhs tidak null -->
                                            <tr>
                                                <td>{{ $mhs->nim ?? 'N/A' }}</td>
                                                <td>{{ $mhs->nama ?? 'N/A' }}</td>
                                                <td>{{ $selectedKelas->kode_mk }}</td>
                                                <td>{{ $namaMk }}</td>
                                                <td>
                                                    <a href="{{ route('nilai.mahasiswa.index', ['nim' => $mhs->nim ?? '', 'kode_mk' => $selectedKelas->kode_mk]) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i> Input Nilai
                                                    </a>
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

    <style>
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
    </style>
@endsection
