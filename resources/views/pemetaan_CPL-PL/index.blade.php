@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPL - PL')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pemetaan CPL - PL</h3>
            </div>
            <div class="card-body">
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
                                            @if ($cpl->pls->contains($pl->id)) checked @endif>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#pemetaanTable").DataTable({
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

            // Event ketika checkbox berubah
            $(".update-mapping").on("change", function() {
                var cpl_id = $(this).data("cpl");
                var pl_id = $(this).data("pl");
                var checked = $(this).prop("checked");

                $.ajax({
                    url: "{{ route('Cpl_Pl.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        cpl_id: cpl_id,
                        pl_id: pl_id,
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
        });
    </script>
@endsection
