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
                    <select id="mkSelect" class="pembobotan-mk-select form-control">
                        <option value="" disabled>-- Pilih Mata Kuliah --</option>
                        @foreach ($mks as $mk)
                            <option value="{{ $mk->id }}" @if ($defaultMk && $defaultMk->id == $mk->id) selected @endif>
                                {{ $mk->kode_mk }} - {{ $mk->deskripsi }}
                            </option>
                        @endforeach
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
        // Hitung total bobot secara otomatis saat halaman dimuat
        updateTotalBobot();

        // Pastkan tidak ada konflik dengan event listener lain
        $('#mkSelect').off('change').on('change', function() {
            var mk_id = $(this).val();
            console.log('Selected MK ID:', mk_id);
            if (mk_id) {
                $.ajax({
                    url: '{{ route('pembobotan.get-cpmks', ':mk_id') }}'.replace(':mk_id', mk_id),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('AJAX Response:', response);
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
                        updateTotalBobot(); // Hitung total bobot setelah memuat data baru
                        $('#simpanBobot').prop('disabled', false);
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat CPMK! Status: ' + xhr.status + ', Response: ' + xhr.responseText, {
                            position: 'top-right',
                            timeOut: 5000,
                            progressBar: true,
                            iconClass: 'toast-error'
                        });
                        console.error('AJAX Error:', xhr.status, xhr.responseText);
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
                let bobot = parseInt($(this).val()) || 0; // Gunakan parseInt untuk integer
                if (isNaN(bobot)) {
                    bobot = 0; // Pastkan tidak ada NaN
                }
                total += bobot;
                console.log('Bobot for cpmk_id ' + $(this).data('cpmk') + ': ' + bobot + ', type: ' + typeof bobot);
            });
            $('#totalBobot').text(total.toFixed(0)); // Tampilkan tanpa desimal untuk integer
            $('#simpanBobot').prop('disabled', total !== 100);
        }

        // Perbarui total bobot saat input berubah
        $(document).on('input', '.bobot-input', function() {
            updateTotalBobot();
        });

        // Simpan bobot dan refresh data setelah berhasil
        $('#simpanBobot').click(function() {
            let bobotData = [];
            let mk_id = $('#mkSelect').val();
            console.log('Saving bobot with mk_id:', mk_id);

            if (!mk_id) {
                toastr.error('Pilih mata kuliah terlebih dahulu!', {
                    position: 'top-right',
                    timeOut: 5000,
                    progressBar: true,
                    iconClass: 'toast-error'
                });
                return;
            }

            $('.bobot-input').each(function() {
                let cpmk_id = $(this).data('cpmk');
                let bobot = $(this).val();
                if (cpmk_id && bobot) {
                    let parsedBobot = parseInt(bobot) || 0;
                    if (isNaN(parsedBobot)) {
                        parsedBobot = 0; // Pastkan tidak ada NaN
                    }
                    bobotData.push({ cpmk_id, bobot: parsedBobot });
                    console.log('Sending bobot for cpmk_id: ' + cpmk_id + ', bobot: ' + parsedBobot + ', type: ' + typeof parsedBobot);
                }
            });

            if (bobotData.length > 0) {
                $.ajax({
                    url: "{{ route('pembobotan.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        mk_id: mk_id,
                        bobotData: bobotData
                    },
                    success: function(response) {
                        toastr.success("✅ Data berhasil disimpan!", {
                            position: 'top-right',
                            timeOut: 5000,
                            progressBar: true,
                            iconClass: 'toast-success'
                        });
                        // Refresh tabel untuk memuat data bobot terbaru
                        $.ajax({
                            url: '{{ route('pembobotan.get-cpmks', ':mk_id') }}'.replace(':mk_id', mk_id),
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                console.log('Refresh AJAX Response:', response);
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
                                updateTotalBobot(); // Hitung ulang total bobot setelah refresh
                                $('#simpanBobot').prop('disabled', false);
                            },
                            error: function(xhr) {
                                toastr.error('Gagal refresh data setelah simpan! Status: ' + xhr.status + ', Response: ' + xhr.responseText, {
                                    position: 'top-right',
                                    timeOut: 5000,
                                    progressBar: true,
                                    iconClass: 'toast-error'
                                });
                                console.error('Refresh AJAX Error:', xhr.status, xhr.responseText);
                            }
                        });
                    },
                    error: function(xhr) {
                        var response = JSON.parse(xhr.responseText);
                        toastr.error(response.error, {
                            position: 'top-right',
                            timeOut: 5000,
                            progressBar: true,
                            iconClass: 'toast-error'
                        });
                        console.error('AJAX Error:', xhr.responseText);
                    }
                });
            } else {
                toastr.error('Tidak ada data bobot yang valid untuk disimpan!', {
                    position: 'top-right',
                    timeOut: 5000,
                    progressBar: true,
                    iconClass: 'toast-error'
                });
            }
        });
    });
</script>
@endsection