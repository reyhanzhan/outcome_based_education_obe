@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPMK - CPL')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pemetaan CPMK - CPL</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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
                </div>  
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#pemetaanTable").DataTable({
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

            // ✅ Event ketika checkbox berubah (AJAX)
            $(".update-mapping").on("change", function() {
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
