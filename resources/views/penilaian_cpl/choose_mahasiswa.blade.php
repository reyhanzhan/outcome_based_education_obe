@extends('layouts_adminlte.app')

@section('title', 'Pilih Mahasiswa untuk Penilaian CPL')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Pilih Mahasiswa untuk Penilaian CPL (Periode: {{ $periode }})</h3>
                    <a href="{{ route('penilaian.cpl.choose_periode_dan_kelas') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if ($mahasiswas->isEmpty())
                        <div class="alert alert-warning">Tidak ada mahasiswa yang terdaftar pada periode dan kelas ini.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Program Studi</th>
                                        <th>Kelas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mahasiswas as $mahasiswa)
                                        <tr>
                                            <td>{{ $mahasiswa->nim }}</td>
                                            <td>{{ $mahasiswa->nama }}</td>
                                            <td>{{ $mahasiswa->program_studi }}</td>
                                            <td>{{ $mahasiswa->kelas }}</td>
                                            <td>
                                                <a href="{{ route('penilaian.cpl.index', $mahasiswa->id) }}"
                                                   class="btn btn-sm btn-primary">Lihat Penilaian CPL</a>
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