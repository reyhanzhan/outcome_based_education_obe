@extends('layouts_adminlte.app')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-center align-items-center flex-wrap">
                    <div class="mx-3">
                        <a href="{{ route('mahasiswa.create') }}" class="btn btn-success" data-toggle="tooltip" title="Tambah data mahasiswa baru">
                            <i class="fas fa-plus"></i> Tambah Mahasiswa
                        </a>
                    </div>
                    <div class="mx-3">
                        <a href="{{ route('mahasiswa.template') }}" class="btn btn-info" data-toggle="tooltip" title="Download template Excel untuk impor data">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                    <div class="mx-3">
                        <form action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block">
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
                        <table id="mahasiswaTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 10%;">NIM</th>
                                    <th style="width: 15%;">Nama</th>
                                    <th style="width: 10%;">Periode Masuk</th>
                                    <th style="width: 10%;">Sistem Kuliah</th>
                                    <th style="width: 15%;">Jalur Penerimaan</th>
                                    <th style="width: 10%;">Gelombang Daftar</th>
                                    <th style="width: 10%;">Agama</th>
                                    <th style="width: 10%;">Kode Prodi</th>
                                    <th style="width: 15%; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mahasiswas as $index => $mahasiswa)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $mahasiswa->nim }}</td>
                                        <td>{{ $mahasiswa->nama }}</td>
                                        <td>{{ $mahasiswa->periode_masuk }}</td>
                                        <td>{{ $mahasiswa->sistem_kuliah }}</td>
                                        <td>{{ $mahasiswa->jalur_penerimaan }}</td>
                                        <td>{{ $mahasiswa->gelombang_daftar }}</td>
                                        <td>{{ $mahasiswa->agama }}</td>
                                        <td>{{ $mahasiswa->kode_prodi }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('mahasiswa.edit', $mahasiswa->id) }}" class="btn btn-warning btn-sm mr-1" data-toggle="tooltip" title="Edit data">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('mahasiswa.destroy', $mahasiswa->id) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Hapus data">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Periode Masuk</th>
                                    <th>Sistem Kuliah</th>
                                    <th>Jalur Penerimaan</th>
                                    <th>Gelombang Daftar</th>
                                    <th>Agama</th>
                                    <th>Kode Prodi</th>
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
            // Inisialisasi DataTables
            $("#mahasiswaTable").DataTable({
                "responsive": true,
                "autoWidth": false,
                "paging": true,
                "lengthMenu": [10, 25, 50, 100],
                "pageLength": 10,
                "searching": true,
                "info": true,
                "ordering": true,
                "columnDefs": [{
                        "orderable": true,
                        "targets": 0 // Kolom No bisa diurutkan
                    }, {
                        "orderable": false,
                        "targets": "_all" // Kolom lain tidak diurutkan
                    }],
                "language": {
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Tidak ada data, Mohon buat atau import data terlebih dahulu",
                    "info": "Menampilkan _START_ hingga _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data tersedia",
                    "infoFiltered": "(Disaring dari _MAX_ total data)",
                    "searchPlaceholder": "Cari Mahasiswa...",
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