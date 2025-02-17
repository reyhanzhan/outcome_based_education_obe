@extends('layouts_adminlte.app')

@section('title', 'Sub-CPMK')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <a href="{{ route('subcpmk.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Tambah Sub-CPMK
                    </a>
                </div>
                <div class="card-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Sub-CPMK</th>
                                <th>Uraian</th>
                                <th>CPMK</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subcpmks as $index => $subcpmk)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $subcpmk->kode_subcpmk }}</td>
                                    <td>{{ $subcpmk->uraian }}</td>
                                    <td>{{ $subcpmk->cpmk->kode_cpmk ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('subcpmk.edit', $subcpmk->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('subcpmk.destroy', $subcpmk->id) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
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
                                <th>Kode Sub-CPMK</th>
                                <th>Uraian</th>
                                <th>CPMK</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
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
                    { "orderable": true, "targets": 0 }, // Hanya kolom No yang bisa sorting
                    { "orderable": false, "targets": "_all" } // Nonaktifkan sorting di semua kolom lain
                ],
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
                "language": {
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data tersedia",
                    "infoFiltered": "(Disaring dari _MAX_ total data)",
                    "searchPlaceholder": "Cari Sub-CPMK...",
                    "search": "",
                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api();
                    // Menyalin header ke footer
                    $(api.table().footer()).find('th').each(function (index) {
                        $(this).text($(api.column(index).header()).text());
                    });
                }
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
@endsection
