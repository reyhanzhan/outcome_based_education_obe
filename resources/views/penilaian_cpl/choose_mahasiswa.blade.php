@extends('layouts_adminlte.app')

@section('title', 'Pilih Mahasiswa untuk Penilaian CPL')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pilih Mahasiswa untuk Penilaian CPL di {{ $mk->kode_mk }} - {{ $mk->deskripsi }}</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Mahasiswa</th>
                                    <th>NIM</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mahasiswas as $mahasiswa)
                                    <tr>
                                        <td>{{ $mahasiswa->nama }}</td>
                                        <td>{{ $mahasiswa->nim }}</td>
                                        <td>
                                            <a href="{{ route('penilaian.cpl.radar', [$mahasiswa->id, $mk->id]) }}" class="btn btn-primary">Lihat Grafik Radar CPL</a>
                                        </td>
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