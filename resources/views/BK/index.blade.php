@extends('layouts_adminlte.app')

@section('title', 'Bahan Kajian')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-center align-items-center flex-wrap">
                    <div class="mx-3">
                        <a href="{{ route('bk.create') }}" class="btn btn-success" data-toggle="tooltip" title="Tambah data bahan kajian baru">
                            <i class="fas fa-plus"></i> Tambah Bahan Kajian
                        </a>
                    </div>
                    <div class="mx-3">
                        <a href="{{ route('bk.template') }}" class="btn btn-info" data-toggle="tooltip" title="Download template Excel untuk impor data">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                    <div class="mx-3">
                        <form action="{{ route('bk.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block">
                            @csrf
                            <div class="custom-file" style="width: 200px;">
                                <input type="file" class="custom-file-input" id="file" name="file" accept=".xls,.xlsx,.csv" required>
                                <label class="custom-file-label" for="file"><i class="fas fa-folder-open fa-sm mr-1"></i> Pilih File</label>
                            </div>
                            <button type="submit" class="btn btn-primary ml-2" data-toggle="tooltip" title="Impor data dari file Excel">
                                <i class="fas fa-upload"></i> Impor
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 15%;">Kode BK</th>
                                    <th style="width: 65%;">Deskripsi</th>
                                    <th style="width: 15%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bk as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->kode_bk }}</td>
                                        <td>{{ $item->deskripsi }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('bk.edit', $item->id) }}" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Edit data">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('bk.destroy', $item->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Hapus data">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Kode BK</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </tfoot>
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
        $("#example1").DataTable({
            "responsive": true,
            "autoWidth": false,
            "paging": true,
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": 10,
            "searching": true,
            "info": true,
            "ordering": true,
            "columnDefs": [
                { "orderable": true, "targets": 0 },
                { "orderable": false, "targets": "_all" }
            ],
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(Disaring dari _MAX_ total data)",
                "searchPlaceholder": "Cari Bahan Kajian...",
                "search": "",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            },
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api();
                $(api.table().footer()).find('th').each(function(index) {
                    $(this).text($(api.column(index).header()).text());
                });
            }
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

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