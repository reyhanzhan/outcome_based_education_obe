@extends('layouts_adminlte.app')

@section('title', 'Penilaian CPL')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Penilaian CPL untuk {{ $mahasiswa->nama }}</h3>
                    <a href="{{ route('penilaian.cpl.choose_mahasiswa') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Pilih Mahasiswa
                    </a>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Kode CPL</th>
                                    <th>Deskripsi CPL</th>
                                    <th>Nilai CPL</th>
                                    <th>Pencapaian CPL (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cplData as $cpl)
                                <tr>
                                    <td>{{ $cpl['kode_cpl'] }}</td>
                                    <td>{{ $cpl['deskripsi'] }}</td>
                                    <td>{{ number_format($cpl['nilai_cpl'], 2) }}</td>
                                    <td>{{ number_format($cpl['pencapaian_cpl'], 2) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-right">
                        <a href="{{ route('penilaian.cpl.choose_mahasiswa') }}" class="btn btn-primary">Pilih Mahasiswa Lain</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
