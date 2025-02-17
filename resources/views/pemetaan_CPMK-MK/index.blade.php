@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPMK - MK')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pemetaan CPMK - MK</h3>
            </div>
            <div class="card-body">
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
                                                @if ($cpmk->mks->contains($mk->id)) checked @endif>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>  
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
                "scrollX": true,  // ✅ Tambahkan scroll horizontal agar tabel tidak keluar
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

            // ✅ Event delegation untuk checkbox agar tetap berfungsi setelah pagination
            $(document).on("change", ".update-mapping", function() {
                var cpmk_id = $(this).data("cpmk");
                var mk_id = $(this).data("mk");
                var checked = $(this).prop("checked");

                $.ajax({
                    url: "{{ route('Cpmk_Mk.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        cpmk_id: cpmk_id,
                        mk_id: mk_id,
                        checked: checked ? 1 : 0 // Kirim 1 jika dicentang, 0 jika dihapus
                    },
                    success: function(response) {
                        if (checked) {
                            toastr.success("✅ Data berhasil disimpan!");
                        } else {
                            toastr.warning("❌ Data telah dihapus!");
                        }
                    },
                    error: function() {
                        toastr.error("Gagal menyimpan perubahan!");
                    }
                });
            });
        });
    </script>
@endsection
