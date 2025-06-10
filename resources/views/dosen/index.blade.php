@extends('layouts_adminlte.app')

@section('title', 'Daftar Dosen')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h3 class="card-title text-white">Daftar Dosen</h3>
                <div style="margin-left: 680px">
                    <a href="{{ route('dosen.create') }}" class="btn btn-success btn-sm custom-btn">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Dosen
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($dosens->isEmpty())
                    <div class="alert alert-warning">Tidak ada data dosen tersedia.</div>
                @else --}}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Kode Prodi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dosens as $dosen)
                                <tr>
                                    <td>{{ $dosen->name }}</td>
                                    <td>{{ $dosen->nip }}</td>
                                    <td>{{ $dosen->kode_prodi }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-warning btn-sm edit-dosen-btn custom-action-btn" data-id="{{ $dosen->id }}">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                        <form action="{{ route('dosen.destroy', $dosen->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm custom-action-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus dosen ini?')">
                                                <i class="fas fa-trash-alt mr-1"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            
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
                    <span aria-hidden="true">×</span>
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
                    <button type="button" class="btn btn-secondary custom-btn" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary custom-btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Styling untuk tombol Tambah Dosen */
    .custom-btn {
        transition: all 0.3s ease;
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .custom-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* Styling untuk tombol Edit dan Hapus */
    .custom-action-btn {
        margin-right: 10px; /* Jarak antar tombol */
        border-radius: 20px;
        padding: 6px 15px;
        transition: all 0.3s ease;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .custom-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* Menyesuaikan warna tombol Edit */
    .btn-warning.custom-action-btn {
        background-color: #f7c948;
        border-color: #f7c948;
    }

    .btn-warning.custom-action-btn:hover {
        background-color: #e6b800;
        border-color: #e6b800;
    }

    /* Menyesuaikan warna tombol Hapus */
    .btn-danger.custom-action-btn {
        background-color: #ff4d4f;
        border-color: #ff4d4f;
    }

    .btn-danger.custom-action-btn:hover {
        background-color: #f5222d;
        border-color: #f5222d;
    }

    /* Menyesuaikan warna tombol Simpan dan Batal di modal */
    .btn-primary.custom-btn {
        background-color: #1890ff;
        border-color: #1890ff;
    }

    .btn-primary.custom-btn:hover {
        background-color: #40a9ff;
        border-color: #40a9ff;
    }

    .btn-secondary.custom-btn {
        background-color: #8c8c8c;
        border-color: #8c8c8c;
    }

    .btn-secondary.custom-btn:hover {
        background-color: #bfbfbf;
        border-color: #bfbfbf;
    }
</style>
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