@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPMK - CPL')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            {{-- <div class="card-header d-flex justify-content-between align-items-center"> --}}
                <div class="card-header d-flex justify-content-center align-items-center flex-wrap">
                {{-- <h3 class="card-title">Pemetaan CPMK - CPL</h3> --}}
                <div class="d-flex flex-wrap">
                    <div class="mx-2">
                        <a href="{{ route('cpmk_cpl.template') }}" class="btn btn-info" data-toggle="tooltip" title="Download template Excel untuk impor data">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                    <div class="mx-2">
                        <form action="{{ route('cpmk_cpl.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block">
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
            </div>
            <div class="card-body">
                <div class="table-responsive"> <!-- ✅ Tambahkan ini agar tabel bisa di-scroll horizontal -->
                    <table id="pemetaanTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle text-center">No</th>
                                <th rowspan="2" class="align-middle text-center">Kode CPMK</th>
                                <th colspan="{{ count($cpls) }}" class="text-center">Capaian Profil Lulusan (CPL)</th>
                            </tr>
                            <tr>
                                @foreach ($cpls as $cpl)
                                    <th class="text-center">{{ $cpl->kode_cpl }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cpmks as $index => $cpmk)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $cpmk->kode_cpmk }}</td>
                                    @foreach ($cpls as $cpl)
                                        <td class="text-center">
                                            <input type="checkbox" class="update-mapping"
                                                data-cpmk="{{ $cpmk->id }}"
                                                data-cpl="{{ $cpl->id }}"
                                                @if ($cpmk->cpls->contains($cpl->id)) checked @endif>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- ✅ Akhiran div table-responsive -->
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

            // Event delegation untuk checkbox agar tetap berfungsi setelah pagination
            $(document).on("change", ".update-mapping", function() {
                var cpmk_id = $(this).data("cpmk");
                var cpl_id = $(this).data("cpl");
                var checked = $(this).prop("checked");

                $.ajax({
                    url: "{{ route('Cpmk_Cpl.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        cpmk_id: cpmk_id,
                        cpl_id: cpl_id,
                        checked: checked ? 1 : 0 // Kirim 1 jika dicentang, 0 jika dihapus
                    },
                    success: function(response) {
                        if (checked) {
                            toastr.success("Data berhasil disimpan!", "Sukses");
                        } else {
                            toastr.warning("Data telah dihapus!", "Perhatian");
                        }
                    },
                    error: function() {
                        toastr.error("Gagal menyimpan perubahan", "Error");
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
@endsection