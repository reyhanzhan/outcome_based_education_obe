@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPMK - MK')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-center align-items-center flex-wrap">
                    {{-- <h3 class="card-title">Pemetaan CPMK - MK - {{ Auth::user()->programStudi->nama_prodi ?? 'Prodi Tidak Ditemukan' }}</h3> --}}
                    <div class="d-flex flex-wrap">
                        <div class="mx-3">
                            <a href="{{ route('cpmk_mk.template') }}" class="btn btn-info" data-toggle="tooltip" title="Download template Excel untuk impor data pemetaan">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                        <div class="mx-3">
                            <form action="{{ route('cpmk_mk.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block">
                                @csrf
                                <input type="hidden" name="tahun" value="{{ request('tahun', session('selected_year', '')) }}">
                                <div class="custom-file" style="width: 200px;">
                                    <input type="file" class="custom-file-input" id="file" name="file" accept=".xls,.xlsx,.csv" required>
                                    <label class="custom-file-label" for="file"><i class="fas fa-folder-open fa-sm mr-1"></i> Pilih File</label>
                                </div>
                                <button type="submit" class="btn btn-primary ml-2" data-toggle="tooltip" title="Impor data pemetaan dari file Excel">
                                    <i class="fas fa-upload"></i> Impor
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($cpmks->isEmpty() || $mks->isEmpty())
                        <div class="alert alert-warning">
                            Tidak ada data CPMK atau MK untuk dipetakan. Silakan tambahkan data terlebih dahulu.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table id="pemetaanTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle text-center">Mata Kuliah (MK)</th>
                                        <th colspan="{{ count($cpmks) }}" class="text-center">Capaian Pembelajaran Mata Kuliah (CPMK)</th>
                                    </tr>
                                    <tr>
                                        @foreach ($cpmks as $cpmk)
                                            <th class="text-center">{{ $cpmk->kode_cpmk }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mks as $mk)
                                        <tr>
                                            <td class="align-middle">{{ $mk->kode_mk }} - {{ $mk->deskripsi }}</td>
                                            @foreach ($cpmks as $cpmk)
                                                <td class="text-center">
                                                    <input type="checkbox" class="update-mapping"
                                                           data-cpmk="{{ $cpmk->id }}"
                                                           data-mk="{{ $mk->id }}"
                                                           @if (isset($pemetaan[$cpmk->id . '-' . $mk->id]))
                                                               checked
                                                               data-toggle="tooltip"
                                                               title="Bobot: {{ $pemetaan[$cpmk->id . '-' . $mk->id]['bobot'] }}%, Min Standard: {{ $pemetaan[$cpmk->id . '-' . $mk->id]['min_standard'] }}%"
                                                           @endif>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            var table = $("#pemetaanTable").DataTable({
                "scrollX": true,
                "paging": true,
                "lengthMenu": [10, 25, 50, 100],
                "pageLength": 10,
                "searching": true,
                "info": true,
                "ordering": false,
                "autoWidth": false,
                "language": {
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data tersedia",
                    "infoFiltered": "(Disaring dari _MAX_ total data)",
                    "searchPlaceholder": "Cari data...",
                    "search": "",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });

            // Inisialisasi tooltip
            $('[data-toggle="tooltip"]').tooltip();

            $(document).on("change", ".update-mapping", function() {
                var cpmk_id = $(this).data("cpmk");
                var mk_id = $(this).data("mk");
                var checked = $(this).is(":checked");
                var checkedValue = checked ? 1 : 0;
                console.log("Sending data: ", { cpmk_id, mk_id, checked: checkedValue });

                $.ajax({
                    url: "{{ route('Cpmk_Mk.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        cpmk_id: cpmk_id,
                        mk_id: mk_id,
                        checked: checkedValue,
                        bobot: 0, // Default untuk saat ini
                        min_standard: 50 // Default untuk saat ini
                    },
                    success: function(response) {
                        console.log("Success response: ", response);
                        if (response.success) {
                            toastr.success(response.success, "Sukses");
                            // Refresh tooltip setelah data tersimpan
                            if (checked) {
                                $(this).attr('data-toggle', 'tooltip');
                                $(this).attr('title', 'Bobot: 0%, Min Standard: 50%');
                                $(this).tooltip('dispose').tooltip();
                            } else {
                                $(this).removeAttr('data-toggle');
                                $(this).removeAttr('title');
                                $(this).tooltip('dispose');
                            }
                        } else if (response.error) {
                            toastr.error(response.error, "Error");
                            $(this).prop("checked", !checked);
                        }
                    },
                    error: function(xhr) {
                        console.log("Error response: ", xhr.responseText);
                        var errorMsg = xhr.responseJSON?.error || "Terjadi kesalahan saat memproses pemetaan.";
                        toastr.error(errorMsg, "Error");
                        $(this).prop("checked", !checked);
                    }
                });
            });

            // Custom file input label
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Aktifkan tooltip
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    @if (session('success'))
        <script>
            toastr.success('{{ session('success') }}', "Sukses", { position: 'top-right', timeOut: 5000 });
        </script>
    @endif
    @if (session('error'))
        <script>
            toastr.error('{{ session('error') }}', "Error", { position: 'top-right', timeOut: 5000 });
        </script>
    @endif
@endsection