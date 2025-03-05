@extends('layouts_adminlte.app')

@section('title', 'Pilih Mata Kuliah untuk Penilaian CPL')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pilih Mata Kuliah untuk Penilaian CPL</h3>
                </div>
                <div class="card-body">
                    @if ($mks->isEmpty())
                        <div class="alert alert-warning">Tidak ada Mata Kuliah yang tersedia. Silakan tambahkan Mata Kuliah terlebih dahulu.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kode MK</th>
                                        <th>Deskripsi MK</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mks as $mk)
                                        <tr>
                                            <td>{{ $mk->kode_mk }}</td>
                                            <td>{{ $mk->deskripsi }}</td>
                                            <td>
                                                <a href="{{ route('penilaian.cpl.index', $mk->id) }}" class="btn btn-primary">Lihat Penilaian CPL</a>
                                            </td>
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