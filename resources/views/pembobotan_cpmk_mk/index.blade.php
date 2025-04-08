@extends('layouts_adminlte.app')

@section('title', 'Pembobotan CPMK - MK')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pembobotan CPMK - MK</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="mkSelect">Pilih Mata Kuliah:</label>
                    <select id="mkSelect" class="form-control">
                        <option value="">-- Pilih Mata Kuliah --</option> 
                        @foreach ($mks as $mk)
                            <option value="{{ $mk->id }}" @if ($defaultMk && $defaultMk->id == $mk->id) selected @endif>
                                {{ $mk->kode_mk }} - {{ $mk->deskripsi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tambahkan field untuk memilih jumlah penilaian -->
                <div class="form-group">
                    <label for="jumlahPenilaian">Pilih Tahap Penilaian:</label>
                    <select id="jumlahPenilaian" class="form-control" name="jumlah_penilaian">
                        @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}" @if ($defaultMk && $defaultMk->jumlah_penilaian == $i) selected @elseif ($i == 3 && !$defaultMk) selected @endif>{{ $i }} Kali Penilaian</option>
                        @endfor
                    </select>
                </div>

                <div class="table-responsive">
                    <table id="pembobotanTable" class="table table-bordered table-hover">
                        <tbody id="cpmkTableBody">
                            @if ($defaultMk && count($cpmks) > 0)
                                @foreach ($cpmks as $cpmk)
                                    <!-- Tambahkan thead untuk setiap CPMK -->
                                    <thead>
                                        <tr>
                                            <th>CPMK</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tr class="cpmk-row">
                                        <td>{{ $cpmk->kode_cpmk }}</td>
                                        <td>{{ $cpmk->deskripsi ?? 'Deskripsi tidak tersedia' }}</td>
                                    </tr>
                                    <!-- Tabel Teknik Penilaian -->
                                    <tr>
                                        <td colspan="2">
                                            <h5>Teknik Penilaian untuk {{ $cpmk->kode_cpmk }}</h5>
                                            <table class="table table-bordered teknik-penilaian-table" data-cpmk="{{ $cpmk->id }}">
                                                <thead>
                                                    <tr>
                                                        <th>Teknik Penilaian</th>
                                                        <th>Bobot (%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach (['Kehadiran', 'Kuis', 'Tugas', 'Presentasi', 'UTS', 'UAS', 'Tugas Kelompok'] as $teknik)
                                                        <tr>
                                                            <td>{{ $teknik }}</td>
                                                            <td>
                                                                <input type="number" class="teknik-bobot-input form-control"
                                                                    data-cpmk="{{ $cpmk->id }}" data-teknik="{{ $teknik }}"
                                                                    value="{{ $cpmk->teknik_penilaian[$teknik] ?? 0 }}"
                                                                    min="0" max="100" step="1">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <p class="total-bobot-teknik-p"><strong>Total Bobot Teknik ({{ $cpmk->kode_cpmk }}): <span class="total-bobot-teknik" data-cpmk="{{ $cpmk->id }}">0</span>%</strong></p>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="2" class="text-center">Tidak ada data CPMK untuk MK ini</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <p class="mt-2"><strong>Total Bobot CPMK: <span id="totalBobot">0</span>%</strong></p>
                <button id="simpanBobot" class="btn btn-success mt-3"
                    @if (!$defaultMk || count($cpmks) == 0) disabled @endif>Simpan Pembobotan</button>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let debounceTimeout; // Variabel untuk debounce

        // Inisialisasi Select2
        $('#mkSelect').select2({
            placeholder: "-- Pilih Mata Kuliah --",
            allowClear: false,
            width: '100%'
        });

        $('#jumlahPenilaian').select2({
            placeholder: "-- Pilih Jumlah Penilaian --",
            allowClear: false,
            width: '100%'
        });

        // Set default jumlah penilaian ke 3 jika belum ada data
        if (!$('#jumlahPenilaian').val()) {
            $('#jumlahPenilaian').val(3).trigger('change');
        }

        // Hitung total bobot saat halaman dimuat
        updateTotalBobot();

        $('#mkSelect').on('change', function() {
            var mk_id = $(this).val();
            console.log('Selected MK ID:', mk_id);
            if (mk_id) {
                $.ajax({
                    url: '{{ route('pembobotan.get-cpmks', ':mk_id') }}'.replace(':mk_id', mk_id),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let html = '';
                        if (response.length > 0) {
                            response.forEach(function(cpmk) {
                                html += `
                                    <thead>
                                        <tr>
                                            <th>CPMK</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tr class="cpmk-row">
                                        <td>${cpmk.kode_cpmk || 'Kode tidak tersedia'}</td>
                                        <td>${cpmk.deskripsi || 'Deskripsi tidak tersedia'}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <h5>Teknik Penilaian untuk ${cpmk.kode_cpmk}</h5>
                                            <table class="table table-bordered teknik-penilaian-table" data-cpmk="${cpmk.id}">
                                                <thead>
                                                    <tr>
                                                        <th>Teknik Penilaian</th>
                                                        <th>Bobot (%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Kehadiran</td>
                                                        <td>
                                                            <input type="number" class="teknik-bobot-input form-control"
                                                                data-cpmk="${cpmk.id}" data-teknik="Kehadiran"
                                                                value="${cpmk.teknik_penilaian?.Kehadiran || 0}" min="0" max="100" step="1">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Kuis</td>
                                                        <td>
                                                            <input type="number" class="teknik-bobot-input form-control"
                                                                data-cpmk="${cpmk.id}" data-teknik="Kuis"
                                                                value="${cpmk.teknik_penilaian?.Kuis || 0}" min="0" max="100" step="1">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tugas</td>
                                                        <td>
                                                            <input type="number" class="teknik-bobot-input form-control"
                                                                data-cpmk="${cpmk.id}" data-teknik="Tugas"
                                                                value="${cpmk.teknik_penilaian?.Tugas || 0}" min="0" max="100" step="1">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Presentasi</td>
                                                        <td>
                                                            <input type="number" class="teknik-bobot-input form-control"
                                                                data-cpmk="${cpmk.id}" data-teknik="Presentasi"
                                                                value="${cpmk.teknik_penilaian?.Presentasi || 0}" min="0" max="100" step="1">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>UTS</td>
                                                        <td>
                                                            <input type="number" class="teknik-bobot-input form-control"
                                                                data-cpmk="${cpmk.id}" data-teknik="UTS"
                                                                value="${cpmk.teknik_penilaian?.UTS || 0}" min="0" max="100" step="1">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>UAS</td>
                                                        <td>
                                                            <input type="number" class="teknik-bobot-input form-control"
                                                                data-cpmk="${cpmk.id}" data-teknik="UAS"
                                                                value="${cpmk.teknik_penilaian?.UAS || 0}" min="0" max="100" step="1">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tugas Kelompok</td>
                                                        <td>
                                                            <input type="number" class="teknik-bobot-input form-control"
                                                                data-cpmk="${cpmk.id}" data-teknik="Tugas Kelompok"
                                                                value="${cpmk.teknik_penilaian?.['Tugas Kelompok'] || 0}" min="0" max="100" step="1">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <p class="total-bobot-teknik-p"><strong>Total Bobot Teknik (${cpmk.kode_cpmk}): <span class="total-bobot-teknik" data-cpmk="${cpmk.id}">0</span>%</strong></p>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = '<tr><td colspan="2" class="text-center">Tidak ada data CPMK untuk MK ini</td></tr>';
                        }
                        $('#cpmkTableBody').html(html);
                        updateTotalBobot();
                        $('#simpanBobot').prop('disabled', true);

                        // Ambil jumlah penilaian dari server
                        $.ajax({
                            url: '{{ route('pembobotan.get-jumlah-penilaian', ':mk_id') }}'.replace(':mk_id', mk_id),
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                $('#jumlahPenilaian').val(response.jumlah_penilaian || 3).trigger('change'); // Default ke 3 jika belum ada data
                            },
                            error: function(xhr) {
                                toastr.error('Gagal memuat jumlah penilaian!');
                            }
                        });
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat CPMK! Status: ' + xhr.status + ', Response: ' . xhr.responseText);
                        $('#cpmkTableBody').html('<tr><td colspan="2" class="text-center">Gagal memuat data CPMK</td></tr>');
                        $('#simpanBobot').prop('disabled', true);
                    }
                });
            } else {
                $('#cpmkTableBody').html('<tr><td colspan="2" class="text-center">Pilih mata kuliah terlebih dahulu</td></tr>');
                $('#simpanBobot').prop('disabled', true);
            }
        });

        function updateTotalBobot() {
            let total = 0;
            $('.teknik-penilaian-table').each(function() {
                let cpmk_id = $(this).data('cpmk');
                let totalTeknik = 0;
                $(this).find('.teknik-bobot-input').each(function() {
                    let bobot = parseInt($(this).val()) || 0;
                    totalTeknik += bobot;
                });
                total += totalTeknik;
                $('.total-bobot-teknik[data-cpmk="' + cpmk_id + '"]').text(totalTeknik);
            });
            $('#totalBobot').text(total);

            if (total < 100) {
                toastr.warning('Total bobot CPMK kurang dari 100%! Silakan sesuaikan.');
                $('#simpanBobot').prop('disabled', true);
            } else if (total > 100) {
                toastr.warning('Total bobot CPMK melebihi 100%! Silakan sesuaikan.');
                $('#simpanBobot').prop('disabled', true);
            } else {
                $('#simpanBobot').prop('disabled', false);
            }
        }

        $(document).on('input', '.teknik-bobot-input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(function() {
                updateTotalBobot();
            }, 300);
        });

        $('#simpanBobot').click(function() {
            let teknikData = [];
            let mk_id = $('#mkSelect').val();
            let jumlahPenilaian = $('#jumlahPenilaian').val() || 3; // Default ke 3 jika tidak ada nilai

            if (!mk_id) {
                toastr.error('Pilih mata kuliah terlebih dahulu!');
                return;
            }

            $('.teknik-penilaian-table').each(function() {
                let cpmk_id = $(this).data('cpmk');
                $(this).find('.teknik-bobot-input').each(function() {
                    let teknik = $(this).data('teknik');
                    let bobot = parseInt($(this).val()) || 0;
                    teknikData.push({ cpmk_id, teknik, bobot });
                });
            });

            if (teknikData.length > 0) {
                $.ajax({
                    url: "{{ route('pembobotan.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        mk_id: mk_id,
                        teknikData: teknikData,
                        jumlah_penilaian: jumlahPenilaian
                    },
                    success: function(response) {
                        toastr.success("✅ Data berhasil disimpan!");
                        updateTotalBobot();
                    },
                    error: function(xhr) {
                        toastr.error('Gagal menyimpan data! ' + (xhr.responseJSON?.error || ''));
                    }
                });
            } else {
                toastr.error('Tidak ada data bobot yang valid untuk disimpan!');
            }
        });
    });
</script>

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

    /* Tambahkan jarak di bawah Total Bobot Teknik */
    .total-bobot-teknik-p {
        margin-bottom: 20px !important; /* Jarak 20px, bisa disesuaikan */
    }
</style>
@endsection