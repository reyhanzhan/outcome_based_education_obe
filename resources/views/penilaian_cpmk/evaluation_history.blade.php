@extends('layouts_adminlte.app')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    Riwayat Evaluasi OBE untuk {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }}) pada {{ $mk->kode_mk }} - {{ $mk->deskripsi }}
                </h3>
            </div>
            <div class="card-body">
                @if ($evaluations->isEmpty())
                    <div class="alert alert-warning">
                        Tidak ada riwayat evaluasi untuk mata kuliah ini.
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Tahun</th>
                                <th>Total Score</th>
                                <th>Tanggal Evaluasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluations as $evaluation)
                                <tr>
                                    <td>{{ $evaluation->periode }}</td>
                                    <td>{{ $evaluation->tahun }}</td>
                                    <td>{{ number_format($evaluation->total_score, 0) }}</td>
                                    <td>{{ $evaluation->created_at }}</td>
                                    <td>
                                        <a href="{{ route('evaluasi.obe.detail', ['mahasiswa_id' => $mahasiswa->id, 'mk_id' => $mk->id, 'evaluation_id' => $evaluation->id]) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                </div>
                <a href="{{ route('penilaian.cpmk.index', ['mahasiswa_id' => $mahasiswa->id, 'mk_id' => $mk->id]) }}"
                   class="btn btn-secondary mt-3">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</section>
@endsection