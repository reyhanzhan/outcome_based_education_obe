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
                        @foreach ($mks as $mk)
                            <option value="{{ $mk->id }}" @if($defaultMk && $defaultMk->id == $mk->id) selected @endif>
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
                            @if($defaultMk && count($cpmks) > 0)
                                @foreach ($cpmks as $cpmk)
                                    <tr>
                                        <td>{{ $cpmk->kode_cpmk }}</td>
                                        <td>{{ $cpmk->deskripsi }}</td>
                                        <td>
                                            <input type="number" class="bobot-input form-control"
                                                data-cpmk="{{ $cpmk->id }}" value="{{ $cpmk->bobot ?? 0 }}" min="0" max="100">
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
                <button id="simpanBobot" class="btn btn-success mt-3" @if(!$defaultMk || count($cpmks) == 0) disabled @endif>Simpan Pembobotan</button>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        updateTotalBobot(); // Hitung total bobot awal

        $('#mkSelect').change(function() {
            var mk_id = $(this).val();
            if (mk_id) {
                $.ajax({
                    url: '/pembobotan/get-cpmks/' + mk_id,
                    type: 'GET',
                    success: function(response) {
                        let html = '';
                        response.forEach(function(cpmk) {
                            html += `
                                <tr>
                                    <td>${cpmk.kode_cpmk}</td>
                                    <td>${cpmk.deskripsi}</td>
                                    <td>
                                        <input type="number" class="bobot-input form-control"
                                            data-cpmk="${cpmk.id}" value="${cpmk.bobot ?? 0}" min="0" max="100">
                                    </td>
                                </tr>
                            `;
                        });

                        $('#cpmkTableBody').html(html);
                        updateTotalBobot();
                        $('#simpanBobot').prop('disabled', false);
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memuat CPMK!');
                    }
                });
            } else {
                $('#cpmkTableBody').html('<tr><td colspan="3" class="text-center">Pilih mata kuliah terlebih dahulu</td></tr>');
                $('#simpanBobot').prop('disabled', true);
            }
        });

        $(document).on('input', '.bobot-input', function() {
            updateTotalBobot();
        });

        function updateTotalBobot() {
            let total = 0;
            $('.bobot-input').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalBobot').text(total);
            $('#simpanBobot').prop('disabled', total !== 100);
        }

        $('#simpanBobot').click(function() {
            let bobotData = [];
            let mk_id = $('#mkSelect').val();

            $('.bobot-input').each(function() {
                let cpmk_id = $(this).data('cpmk');
                let bobot = $(this).val();
                bobotData.push({ cpmk_id, bobot });
            });

            $.ajax({
                url: "{{ route('pembobotan.update') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    mk_id: mk_id,
                    bobotData: bobotData
                },
                success: function(response) {
                    toastr.success(response.success);
                },
                error: function(xhr) {
                    var response = JSON.parse(xhr.responseText);
                    toastr.error(response.error);
                }
            });
        });
    });
</script>
@endsection