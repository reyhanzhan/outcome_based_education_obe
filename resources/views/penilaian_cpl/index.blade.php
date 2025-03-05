@extends('layouts_adminlte.app')

@section('title', 'Penilaian CPL')

@section('css')
    <style>
        .card-header.bg-primary {
            background-color: #007bff !important; /* Biru primer AdminLTE */
            color: #fff;
        }

        .table-bordered th, .table-bordered td {
            vertical-align: middle;
            text-align: center;
        }

        .cpl06-bg { background-color: #d4edda; } /* Hijau muda untuk CPL06 */
        .cpl08-bg { background-color: #cce5ff; } /* Biru muda untuk CPL08 */
        .cpl07-bg { background-color: #f8d7da; } /* Merah muda untuk CPL07 */
        .mk-bg { background-color: #e9ecef; } /* Abu-abu muda untuk MK */
        .cpmk-bg { background-color: #f8f9fa; } /* Abu-abu sangat muda untuk CPMK */

        .bobot-highlight {
            font-weight: bold;
            color: #000000; /* Hitam untuk kontras */
        }

        .bg-light {
            background-color: #f8f9fa; /* Abu-abu sangat muda untuk kontras */
        }

        .below-min {
            color: #dc3545; /* Merah untuk nilai di bawah standar minimum */
            font-weight: bold;
        }

        .btn-secondary {
            background-color: #6c757d; /* Abu-abu sekunder AdminLTE */
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268; /* Abu-abu lebih gelap saat hover */
            border-color: #5a6268;
        }
    </style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Penilaian CPL untuk {{ $mk ? $mk->kode_mk . ' - ' . $mk->deskripsi : 'Semua Mata Kuliah' }}</h3>
                    <a href="{{ route('penilaian.cpl.choose_mk') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Pilih Mata Kuliah</a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="bg-light" style="width: 15%;">Nama Mahasiswa</th>
                                    <!-- Kolom CPMK untuk MK04 -->
                                    <th class="cpmk-bg">CPMK061 (Bobot: 20%, )</th>
                                    <th class="cpmk-bg">CPMK062 (Bobot: 20%, )</th>
                                    <th class="cpmk-bg">CPMK063 (Bobot: 40%, )</th>
                                    <!-- Nilai MK04 -->
                                    <th class="mk-bg bobot-highlight">Nilai MK04</th>
                                    <!-- Kolom CPL untuk MK04 -->
                                    <th class="cpl06-bg bobot-highlight">CPL06 (MK04)</th>
                                    <th class="cpl08-bg bobot-highlight">CPL08 (MK04)</th>
                                    <!-- Kolom CPMK untuk MK34 -->
                                    <th class="cpmk-bg">CPMK063 (Bobot: 30%, )</th>
                                    <th class="cpmk-bg">CPMK071 (Bobot: 40%, )</th>
                                    <th class="cpmk-bg">CPMK072 (Bobot: 30%, )</th>
                                    <!-- Nilai MK34 -->
                                    <th class="mk-bg bobot-highlight">Nilai MK34</th>
                                    <!-- Kolom CPL untuk MK34 -->
                                    <th class="cpl06-bg bobot-highlight">CPL06 (MK34)</th>
                                    <th class="cpl07-bg bobot-highlight">CPL07 (MK34)</th>
                                    <!-- Nilai Gabungan CPL -->
                                    <th class="bg-light bobot-highlight">Nilai Gabungan CPL06 (MK04 & MK34)</th>
                                    <th class="bg-light bobot-highlight">Pencapaian CPL06</th>
                                    <th class="bg-light bobot-highlight">Nilai Gabungan CPL08 (MK04)</th>
                                    <th class="bg-light bobot-highlight">Pencapaian CPL08</th>
                                    <th class="bg-light bobot-highlight">Nilai Gabungan CPL07 (MK34)</th>
                                    <th class="bg-light bobot-highlight">Pencapaian CPL07</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mahasiswas as $mahasiswa)
                                    <tr>
                                        <td class="bg-light">{{ $mahasiswa->nama }}</td>
                                        <!-- Nilai CPMK untuk MK04 -->
                                        <td class="cpmk-bg">{{ number_format($mahasiswa->calculateCpmkScore(1, 1) ?? 0, 0) }}%</td> <!-- CPMK061, MK04 (ID 1) -->
                                        <td class="cpmk-bg">{{ number_format($mahasiswa->calculateCpmkScore(2, 1) ?? 0, 0) }}%</td> <!-- CPMK062, MK04 (ID 1) -->
                                        <td class="cpmk-bg">{{ number_format($mahasiswa->calculateCpmkScore(3, 1) ?? 0, 0) }}%</td> <!-- CPMK063, MK04 (ID 1) -->
                                        <!-- Nilai MK04 -->
                                        <td class="mk-bg">{{ number_format($mk->calculateMkScore($mahasiswa->id) ?? 0, 0) }}%</td> <!-- Asumsi MK04 memiliki ID 1 -->
                                        <!-- Nilai CPL untuk MK04 -->
                                        <td class="cpl06-bg">{{ number_format($mahasiswa->calculateCplScore(1, 1) ?? 0, 0) }}%</td> <!-- CPL06, MK04 (ID 1) -->
                                        <td class="cpl08-bg">{{ number_format($mahasiswa->calculateCplScore(2, 1) ?? 0, 0) }}%</td> <!-- CPL08, MK04 (ID 1) -->
                                        <!-- Nilai CPMK untuk MK34 -->
                                        <td class="cpmk-bg">{{ number_format($mahasiswa->calculateCpmkScore(3, 2) ?? 0, 0) }}%</td> <!-- CPMK063, MK34 (ID 2) -->
                                        <td class="cpmk-bg">{{ number_format($mahasiswa->calculateCpmkScore(4, 2) ?? 0, 0) }}%</td> <!-- CPMK071, MK34 (ID 2) -->
                                        <td class="cpmk-bg">{{ number_format($mahasiswa->calculateCpmkScore(5, 2) ?? 0, 0) }}%</td> <!-- CPMK072, MK34 (ID 2) -->
                                        <!-- Nilai MK34 -->
                                        <td class="mk-bg">{{ number_format($mk->calculateMkScore($mahasiswa->id) ?? 0, 0) }}%</td> <!-- Asumsi MK34 memiliki ID 2 -->
                                        <!-- Nilai CPL untuk MK34 -->
                                        <td class="cpl06-bg">{{ number_format($mahasiswa->calculateCplScore(1, 2) ?? 0, 0) }}%</td> <!-- CPL06, MK34 (ID 2) -->
                                        <td class="cpl07-bg">{{ number_format($mahasiswa->calculateCplScore(3, 2) ?? 0, 0) }}%</td> <!-- CPL07, MK34 (ID 2) -->
                                        <!-- Nilai Gabungan CPL -->
                                        <td class="bg-light">{{ number_format($mahasiswa->calculateCombinedCplScore(1, [1, 2]) ?? 0, 0) }}%</td> <!-- CPL06 dari MK04 & MK34 -->
                                        <td class="bg-light">{{ number_format($mahasiswa->calculateCplAchievement(1, [1, 2]) ?? 0, 0) }}%</td> <!-- Pencapaian CPL06 -->
                                        <td class="bg-light">{{ number_format($mahasiswa->calculateCombinedCplScore(2, [1]) ?? 0, 0) }}%</td> <!-- CPL08 dari MK04 -->
                                        <td class="bg-light">{{ number_format($mahasiswa->calculateCplAchievement(2, [1]) ?? 0, 0) }}%</td> <!-- Pencapaian CPL08 -->
                                        <td class="bg-light">{{ number_format($mahasiswa->calculateCombinedCplScore(3, [2]) ?? 0, 0) }}%</td> <!-- CPL07 dari MK34 -->
                                        <td class="bg-light">{{ number_format($mahasiswa->calculateCplAchievement(3, [2]) ?? 0, 0) }}%</td> <!-- Pencapaian CPL07 -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-right">
                        <a href="{{ route('penilaian.cpl.choose_mahasiswa', $mk ? $mk->id : 1) }}" class="btn btn-primary">Pilih Mahasiswa untuk Grafik Radar</a>
                        <a href="{{ route('penilaian.cpl.radar', [$mahasiswa->id, $mk ? $mk->id : 1]) }}" class="btn btn-primary">Lihat Grafik Radar CPL</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    if (typeof jQuery === 'undefined') {
        console.error('jQuery tidak dimuat!');
    } else {
        $(document).ready(function() {
            // Tidak perlu form di sini, hanya tampilan
        });
    }
</script>
@endsection