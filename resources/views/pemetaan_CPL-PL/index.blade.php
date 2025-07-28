@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPL - PL')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-center align-items-center flex-wrap">
                    <div class="mx-3">
                        <a href="{{ route('cpl_pl.template') }}" class="btn btn-info" data-toggle="tooltip" title="Download template Excel untuk impor data pemetaan">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                    <div class="mx-3">
                        <form action="{{ route('cpl_pl.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block">
                            @csrf
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
                <div class="card-body">
                    @if ($cpls->isEmpty() || $pls->isEmpty())
                        <div class="alert alert-warning">
                            Tidak ada data CPL atau PL untuk dipetakan. Silakan tambahkan data terlebih dahulu.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table id="pemetaanTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle text-center">No</th>
                                        <th rowspan="2" class="align-middle text-center">Kode CPL</th>
                                        <th colspan="{{ count($pls) }}" class="text-center">Profil Lulusan (PL)</th>
                                    </tr>
                                    <tr>
                                        @foreach ($pls as $pl)
                                            <th class="text-center">{{ $pl->kode_pl }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cpls as $index => $cpl)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $cpl->kode_cpl }}</td>
                                            @foreach ($pls as $pl)
                                                <td class="text-center">
                                                    <input type="checkbox" class="update-mapping"
                                                           data-cpl="{{ $cpl->id }}"
                                                           data-pl="{{ $pl->id }}"
                                                           @if (isset($pemetaan[$cpl->id . '-' . $pl->id])) checked @endif>
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
            var table = $("#pemetaanTable").DataTable({
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

            $(document).on("change", ".update-mapping", function() {
                var cpl_id = $(this).data("cpl");
                var pl_id = $(this).data("pl");
                var checked = $(this).is(":checked");
                var checkedValue = checked ? 1 : 0; // Konversi ke 1 atau 0
                console.log("Sending data: ", { cpl_id, pl_id, checked: checkedValue });

                $.ajax({
                    url: "{{ route('Cpl_Pl.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        cpl_id: cpl_id,
                        pl_id: pl_id,
                        checked: checkedValue
                    },
                    success: function(response) {
                        console.log("Success response: ", response);
                        if (response.success) {
                            toastr.success(response.success, "Sukses");
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