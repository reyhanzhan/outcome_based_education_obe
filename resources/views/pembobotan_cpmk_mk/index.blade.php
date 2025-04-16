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

                <div class="table-responsive">
                    <table id="pembobotanTable" class="table table-bordered table-hover">
                        <tbody id="cpmkTableBody">
                            @if ($defaultMk && count($cpmks) > 0)
                                @foreach ($cpmks as $cpmk)
                                    <tr class="cpmk-row">
                                        <td>
                                            <div class="cpmk-label">CPMK</div>
                                            {{ $cpmk->kode_cpmk }}
                                        </td>
                                        <td>
                                            <div class="cpmk-label">Deskripsi</div>
                                            {{ $cpmk->deskripsi ?? 'Deskripsi tidak tersedia' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
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
        let debounceTimeout;

        $('#mkSelect').select2({
            placeholder: "-- Pilih Mata Kuliah --",
            allowClear: false,
            width: '100%'
        });

        updateTotalBobot(); // Hitung total bobot saat halaman dimuat

        $('#mkSelect').on('change', function() {
            var mk_id = $(this).val();
            if (mk_id) {
                $.ajax({
                    url: '{{ route('pembobotan.get-cpmks', ':mk_id') }}'.replace(':mk_id', mk_id),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let html = '';
                        if (response.length > 0) {
                            response.forEach(function(cpmk) {
                                console.log('CPMK Teknik Penilaian:', cpmk.teknik_penilaian); // Debugging
                                html += `
                                    <tr class="cpmk-row">
                                        <td>
                                            <div class="cpmk-label">CPMK</div>
                                            ${cpmk.kode_cpmk || 'Kode tidak tersedia'}
                                        </td>
                                        <td>
                                            <div class="cpmk-label">Deskripsi</div>
                                            ${cpmk.deskripsi || 'Deskripsi tidak tersedia'}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
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
                                                        <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="Kehadiran" value="${cpmk.teknik_penilaian?.Kehadiran || 0}" min="0" max="100" step="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Kuis</td>
                                                        <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="Kuis" value="${cpmk.teknik_penilaian?.Kuis || 0}" min="0" max="100" step="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tugas</td>
                                                        <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="Tugas" value="${cpmk.teknik_penilaian?.Tugas || 0}" min="0" max="100" step="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Presentasi</td>
                                                        <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="Presentasi" value="${cpmk.teknik_penilaian?.Presentasi || 0}" min="0" max="100" step="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>UTS</td>
                                                        <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="UTS" value="${cpmk.teknik_penilaian?.UTS || 0}" min="0" max="100" step="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>UAS</td>
                                                        <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="UAS" value="${cpmk.teknik_penilaian?.UAS || 0}" min="0" max="100" step="1"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tugas Kelompok</td>
                                                        <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="Tugas Kelompok" value="${cpmk.teknik_penilaian?.['Tugas Kelompok'] || 0}" min="0" max="100" step="1"></td>
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
                        $('#simpanBobot').prop('disabled', response.length === 0);
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat CPMK! ' + xhr.responseText);
                    }
                });
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

            if (total !== 100) {
                toastr.warning('Total bobot CPMK harus 100%! Silakan sesuaikan.');
                $('#simpanBobot').prop('disabled', true);
            } else {
                $('#simpanBobot').prop('disabled', false);
            }
        }

        $(document).on('input', '.teknik-bobot-input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(updateTotalBobot, 300);
        });

        $('#simpanBobot').click(function() {
            let teknikData = [];
            let mk_id = $('#mkSelect').val();

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
                        teknikData: teknikData
                    },
                    success: function(response) {
                        toastr.success("✅ Data berhasil disimpan!");
                        $('#mkSelect').trigger('change'); // Reload data setelah simpan
                    },
                    error: function(xhr) {
                        toastr.error('Gagal menyimpan data! ' + (xhr.responseJSON?.error || ''));
                    }
                });
            }
        });
    });
</script>

<style>
    .form-group .select2-container { width: 100% !important; }
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
    .total-bobot-teknik-p { margin-bottom: 20px !important; }
    .cpmk-label {
        font-weight: 700;
        font-size: 1rem;
        color: #212529;
        background-color: #f8f9fa;
        padding: 0.5rem;
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 0.5rem;
    }
    .cpmk-row td { padding: 0.75rem; vertical-align: top; }
</style>
@endsection