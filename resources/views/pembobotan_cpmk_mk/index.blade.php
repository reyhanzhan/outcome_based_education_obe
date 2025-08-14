@extends('layouts_adminlte.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pembobotan CPMK Mata Kuliah</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="kurikulumSelect">Tahun Kurikulum</label>
                                <select id="kurikulumSelect" class="form-control">
                                    @foreach ($kurikulumOptions as $option)
                                        <option value="{{ $option }}" {{ $tahunFilter == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="mkSelect">Pilih Mata Kuliah</label>
                                <select id="mkSelect" class="form-control" data-tahun="{{ $tahunFilter }}">
                                    @if($defaultMk)
                                        <option value="{{ $defaultMk->id }}" selected>
                                            {{ $defaultMk->kode_mk }} - {{ $defaultMk->deskripsi }}
                                        </option>
                                    @else
                                        <option value="" selected>-- Pilih Mata Kuliah --</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>CPMK</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cpmkTableBody">
                                        @if($cpmks && count($cpmks) > 0)
                                            @foreach($cpmks as $cpmk)
                                                <tr class="cpmk-row">
                                                    <td>
                                                        <div class="cpmk-label">CPMK</div>
                                                        {{ $cpmk->kode_cpmk ?? 'Kode tidak tersedia' }}
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
                                                                <tr>
                                                                    <td>Partisipasi(Kuis)</td>
                                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="{{ $cpmk->id }}" data-teknik="Kuis" value="{{ $cpmk->teknik_penilaian['Kuis'] ?? 0 }}" min="0" max="100" step="1"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Observasi(Praktik/Tugas)</td>
                                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="{{ $cpmk->id }}" data-teknik="Tugas" value="{{ $cpmk->teknik_penilaian['Tugas'] ?? 0 }}" min="0" max="100" step="1"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Unjuk Kerja(Presentasi)</td>
                                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="{{ $cpmk->id }}" data-teknik="Presentasi" value="{{ $cpmk->teknik_penilaian['Presentasi'] ?? 0 }}" min="0" max="100" step="1"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Tes Tulis(UTS)</td>
                                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="{{ $cpmk->id }}" data-teknik="UTS" value="{{ $cpmk->teknik_penilaian['UTS'] ?? 0 }}" min="0" max="100" step="1"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Tes Tulis(UAS)</td>
                                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="{{ $cpmk->id }}" data-teknik="UAS" value="{{ $cpmk->teknik_penilaian['UAS'] ?? 0 }}" min="0" max="100" step="1"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Tes Lisan(Tugas Kelompok)</td>
                                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="{{ $cpmk->id }}" data-teknik="Tugas Kelompok" value="{{ $cpmk->teknik_penilaian['Tugas Kelompok'] ?? 0 }}" min="0" max="100" step="1"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <p class="total-bobot-teknik-p"><strong>Total Bobot Teknik ({{ $cpmk->kode_cpmk }}): <span class="total-bobot-teknik" data-cpmk="{{ $cpmk->id }}">0</span>%</strong></p>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td colspan="2" class="text-center">Tidak ada data CPMK untuk MK ini</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                                <div class="row mt-3">
                                    <div class="col-12 text-right">
                                        <p><strong>Total Bobot Keseluruhan: <span id="totalBobot">0</span>%</strong></p>
                                        <button id="simpanBobot" class="btn btn-primary" disabled>Simpan Bobot</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
    let debounceTimeout;

    // Inisialisasi Select2 untuk kurikulum
    $('#kurikulumSelect').select2({
        placeholder: "-- Pilih Tahun Kurikulum --",
        allowClear: false,
        width: '100%'
    }).on('change', function() {
        const selectedYear = $(this).val();
        sessionStorage.setItem('selected_year', selectedYear); // Simpan di sessionStorage sementara
        $.ajax({
            url: '{{ route('pembobotan.update-session-year') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tahun: selectedYear
            },
            success: function(response) {
                // Refresh halaman untuk memuat data baru berdasarkan tahun
                window.location.href = '{{ route("pembobotan.index", ["kode_prodi" => Auth::user()->kode_prodi]) }}' + '?tahun=' + selectedYear;
            },
            error: function(xhr) {
                toastr.error('Gagal memperbarui tahun kurikulum!');
            }
        });
    });

    // Inisialisasi Select2 untuk mata kuliah
    $('#mkSelect').select2({
        placeholder: "-- Pilih Mata Kuliah --",
        allowClear: false,
        width: '100%',
        ajax: {
            url: '{{ route('pembobotan.search-mk') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    tahun: $('#kurikulumSelect').val() || $('#mkSelect').data('tahun'),
                    _token: '{{ csrf_token() }}'
                };
            },
            processResults: function(data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    }).on('change', function() {
        var mk_id = $(this).val();
        var tahunFilter = $('#kurikulumSelect').val() || $(this).data('tahun');
        if (mk_id) {
            $.ajax({
                url: '{{ route('pembobotan.get-cpmks', ':mk_id') }}'.replace(':mk_id', mk_id),
                type: 'GET',
                dataType: 'json',
                data: { tahun: tahunFilter },
                success: function(response) {
                    let html = '';
                    if (response.length > 0) {
                        response.forEach(function(cpmk) {
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
                                                    <td>Partisipasi(Kuis)</td>
                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="Kuis" value="${cpmk.teknik_penilaian?.Kuis || 0}" min="0" max="100" step="1"></td>
                                                </tr>
                                                <tr>
                                                    <td>Observasi(Praktik/Tugas)</td>
                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="Tugas" value="${cpmk.teknik_penilaian?.Tugas || 0}" min="0" max="100" step="1"></td>
                                                </tr>
                                                <tr>
                                                    <td>Unjuk Kerja(Presentasi)</td>
                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="Presentasi" value="${cpmk.teknik_penilaian?.Presentasi || 0}" min="0" max="100" step="1"></td>
                                                </tr>
                                                <tr>
                                                    <td>Tes Tulis(UTS)</td>
                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="UTS" value="${cpmk.teknik_penilaian?.UTS || 0}" min="0" max="100" step="1"></td>
                                                </tr>
                                                <tr>
                                                    <td>Tes Tulis(UAS)</td>
                                                    <td><input type="number" class="teknik-bobot-input form-control" data-cpmk="${cpmk.id}" data-teknik="UAS" value="${cpmk.teknik_penilaian?.UAS || 0}" min="0" max="100" step="1"></td>
                                                </tr>
                                                <tr>
                                                    <td>Tes Lisan(Tugas Kelompok)</td>
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
        let tahunFilter = $('#kurikulumSelect').val() || $('#mkSelect').data('tahun');

        if (!mk_id) {
            toastr.error('Pilih mata kuliah terlebih dahulu!');
            return;
        }

        $('.teknik-penilaian-table').each(function() {
            let cpmk_id = $(this).data('cpmk');
            $(this).find('.teknik-bobot-input').each(function() {
                let teknik = $(this).data('teknik');
                let bobot = parseInt($(this).val()) || 0;
                teknikData.push({
                    cpmk_id,
                    teknik,
                    bobot
                });
            });
        });

        if (teknikData.length > 0) {
            $.ajax({
                url: "{{ route('pembobotan.update') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    mk_id: mk_id,
                    tahun: tahunFilter,
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
        /* hapus panah atas bawah */
        .teknik-bobot-input[type="number"] {
            -moz-appearance: textfield; /* Firefox */
        }

        .teknik-bobot-input[type="number"]::-webkit-inner-spin-button,
        .teknik-bobot-input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        /* hapus panah atas bawah */
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

        .total-bobot-teknik-p {
            margin-bottom: 20px !important;
        }

        .cpmk-label {
            font-weight: 700;
            font-size: 1rem;
            color: #212529;
            background-color: #f8f9fa;
            padding: 0.5rem;
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 0.5rem;
        }

        .cpmk-row td {
            padding: 0.75rem;
            vertical-align: top;
        }
    </style>
@endsection