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
                    <label for="jumlahPenilaian">Jumlah Penilaian:</label>
                    <select id="jumlahPenilaian" class="form-control" name="jumlah_penilaian">
                        <option value="1" @if ($defaultMk && $defaultMk->jumlah_penilaian == 1) selected @endif>1 Kali Penilaian</option>
                        <option value="2" @if ($defaultMk && $defaultMk->jumlah_penilaian == 2) selected @endif>2 Kali Penilaian</option>
                        <option value="3" @if ($defaultMk && $defaultMk->jumlah_penilaian == 3) selected @endif>3 Kali Penilaian</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table id="pembobotanTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>CPMK</th>
                                <th>Deskripsi</th>
                                <th>Bobot (%)</th>
                            </tr>
                        </thead>
                        <tbody id="cpmkTableBody">
                            @if ($defaultMk && count($cpmks) > 0)
                                @foreach ($cpmks as $cpmk)
                                    <tr>
                                        <td>{{ $cpmk->kode_cpmk }}</td>
                                        <td>{{ $cpmk->deskripsi ?? 'Deskripsi tidak tersedia' }}</td>
                                        <td>
                                            <input type="number" class="bobot-input form-control"
                                                data-cpmk="{{ $cpmk->id }}" value="{{ $cpmk->bobot ?? 0 }}"
                                                min="0" max="100" step="1">
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center">Tidak ada data CPMK untuk MK ini</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <p class="mt-2"><strong>Total Bobot: <span id="totalBobot">0</span>%</strong></p>
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
                                    <tr>
                                        <td>${cpmk.kode_cpmk || 'Kode tidak tersedia'}</td>
                                        <td>${cpmk.deskripsi || 'Deskripsi tidak tersedia'}</td>
                                        <td>
                                            <input type="number" class="bobot-input form-control"
                                                data-cpmk="${cpmk.id || ''}" value="${cpmk.bobot !== null ? cpmk.bobot : 0}" min="0" max="100" step="1">
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = '<tr><td colspan="3" class="text-center">Tidak ada data CPMK untuk MK ini</td></tr>';
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
                                $('#jumlahPenilaian').val(response.jumlah_penilaian || 1).trigger('change');
                            },
                            error: function(xhr) {
                                toastr.error('Gagal memuat jumlah penilaian!');
                            }
                        });
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat CPMK! Status: ' + xhr.status + ', Response: ' + xhr.responseText);
                        $('#cpmkTableBody').html('<tr><td colspan="3" class="text-center">Gagal memuat data CPMK</td></tr>');
                        $('#simpanBobot').prop('disabled', true);
                    }
                });
            } else {
                $('#cpmkTableBody').html('<tr><td colspan="3" class="text-center">Pilih mata kuliah terlebih dahulu</td></tr>');
                $('#simpanBobot').prop('disabled', true);
            }
        });

        function updateTotalBobot() {
            let total = 0;
            $('.bobot-input').each(function() {
                let bobot = parseInt($(this).val()) || 0;
                total += bobot;
            });
            $('#totalBobot').text(total);

            if (total < 100) {
                toastr.warning('Total bobot kurang dari 100%! Silakan sesuaikan.');
                $('#simpanBobot').prop('disabled', true);
            } else if (total > 100) {
                toastr.warning('Total bobot melebihi 100%! Silakan sesuaikan.');
                $('#simpanBobot').prop('disabled', true);
            } else {
                $('#simpanBobot').prop('disabled', false);
            }
        }

        $(document).on('input', '.bobot-input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(updateTotalBobot, 3000);
        });

        $('#simpanBobot').click(function() {
            let bobotData = [];
            let mk_id = $('#mkSelect').val();
            let jumlahPenilaian = $('#jumlahPenilaian').val();

            if (!mk_id) {
                toastr.error('Pilih mata kuliah terlebih dahulu!');
                return;
            }

            $('.bobot-input').each(function() {
                let cpmk_id = $(this).data('cpmk');
                let bobot = parseInt($(this).val()) || 0;
                bobotData.push({ cpmk_id, bobot });
            });

            if (bobotData.length > 0) {
                $.ajax({
                    url: "{{ route('pembobotan.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        mk_id: mk_id,
                        bobotData: bobotData,
                        jumlah_penilaian: jumlahPenilaian // Kirim jumlah penilaian
                    },
                    success: function(response) {
                        toastr.success("✅ Data berhasil disimpan!");
                        updateTotalBobot();
                    },
                    error: function(xhr) {
                        toastr.error('Gagal menyimpan data!');
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
</style>
@endsection