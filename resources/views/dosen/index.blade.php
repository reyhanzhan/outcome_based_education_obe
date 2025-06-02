@extends('layouts_adminlte.app')

@section('title', 'Daftar Dosen')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h3 class="card-title">Daftar Dosen</h3>
                <a href="{{ route('dosen.create') }}" class="btn btn-success btn-sm">Tambah Dosen</a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($dosens->isEmpty())
                    <div class="alert alert-warning">Tidak ada data dosen tersedia.</div>
                @else
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Kode Prodi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dosens as $dosen)
                                <tr>
                                    <td>{{ $dosen->name }}</td>
                                    <td>{{ $dosen->nip }}</td>
                                    <td>{{ $dosen->kode_prodi }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm edit-dosen-btn" data-id="{{ $dosen->id }}">Edit</button>
                                        <form action="{{ route('dosen.destroy', $dosen->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus dosen ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Modal untuk Edit Dosen -->
<div class="modal fade" id="editDosenModal" tabindex="-1" role="dialog" aria-labelledby="editDosenModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDosenModalLabel">Edit Dosen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editDosenForm" method="POST">
                <div class="modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_dosen_id">
                    <div class="form-group">
                        <label for="edit_name">Nama Dosen</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nip">NIP</label>
                        <input type="text" name="nip" id="edit_nip" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">Password (kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" name="password" id="edit_password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_kode_prodi">Kode Prodi</label>
                        <input type="text" name="kode_prodi" id="edit_kode_prodi" class="form-control" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.edit-dosen-btn').on('click', function() {
            const dosenId = $(this).data('id');
            $.ajax({
                url: '{{ route("dosen.edit", ":id") }}'.replace(':id', dosenId),
                method: 'GET',
                success: function(data) {
                    $('#edit_dosen_id').val(data.id);
                    $('#edit_name').val(data.name);
                    $('#edit_nip').val(data.nip);
                    $('#edit_kode_prodi').val(data.kode_prodi);
                    $('#editDosenModal').modal('show');
                },
                error: function() {
                    alert('Gagal mengambil data dosen.');
                }
            });
        });

        $('#editDosenForm').on('submit', function(e) {
            e.preventDefault();
            const dosenId = $('#edit_dosen_id').val();
            $.ajax({
                url: '{{ route("dosen.update", ":id") }}'.replace(':id', dosenId),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    window.location.href = '{{ route("dosen.index") }}';
                },
                error: function() {
                    alert('Gagal memperbarui data dosen.');
                }
            });
        });
    });
</script>
@endsection