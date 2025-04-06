@extends('layouts_adminlte.app')

@section('title', 'Penilaian CPMK')

@section('css')
    <style>
        .card-header.bg-primary {
            background-color: #007bff !important;
            color: #fff;
        }

        .table-bordered th, .table-bordered td {
            vertical-align: middle;
            text-align: center;
        }

        .bobot-highlight {
            background-color: #e9ecef;
            font-weight: bold;
            color: #000000;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

        .below-min {
            color: #dc3545;
            font-weight: bold;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        /* Pastikan tab terlihat interaktif */
        .nav-tabs .nav-link {
            cursor: pointer;
        }

        .nav-tabs .nav-link:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        Penilaian CPMK untuk {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }}) pada {{ $mk->kode_mk }} - {{ $mk->deskripsi }}
                    </h3>
                    <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ $periode }}&kelas={{ $kelasInput }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Mahasiswa
                    </a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success success-bg">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger error-bg">{{ session('error') }}</div>
                    @endif

                    <!-- Tampilkan informasi periode dan kelas -->
                    <div class="mb-3">
                        <strong>Periode:</strong> {{ $periode }} <br>
                        <strong>Kelas:</strong> {{ $kelasInput }}
                    </div>

                    @if ($cpmks->isEmpty())
                        <div class="alert alert-warning">
                            Tidak ada CPMK yang terkait dengan mata kuliah ini.
                        </div>
                    @else
                        <!-- Tab Navigasi -->
                        <ul class="nav nav-tabs" id="penilaianTab" role="tablist">
                            @for ($i = 1; $i <= $jumlahPenilaian; $i++)
                                <li class="nav-item">
                                    <a class="nav-link {{ $i == 1 ? 'active' : '' }}" id="penilaian-{{ $i }}-tab" data-toggle="tab" href="#penilaian-{{ $i }}" role="tab" aria-controls="penilaian-{{ $i }}" aria-selected="{{ $i == 1 ? 'true' : 'false' }}">
                                        Penilaian {{ $i }}
                                    </a>
                                </li>
                            @endfor
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="penilaianTabContent">
                            @for ($i = 1; $i <= $jumlahPenilaian; $i++)
                                <div class="tab-pane fade {{ $i == 1 ? 'show active' : '' }}" id="penilaian-{{ $i }}" role="tabpanel" aria-labelledby="penilaian-{{ $i }}-tab">
                                    <div class="table-responsive mt-3">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="bg-light" style="width: 15%;">Nama Mahasiswa</th>
                                                    @foreach ($cpmks as $cpmk)
                                                        <th class="bobot-highlight">
                                                            {{ $cpmk->kode_cpmk }} (Bobot: {{ $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0 }}%)
                                                        </th>
                                                    @endforeach
                                                    <th class="bg-light">Nilaiii Total MK</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="bg-light">{{ $mahasiswa->nama }}</td>
                                                    @foreach ($cpmks as $cpmk)
                                                        <td>
                                                            @php
                                                                $nilaiCpmk = isset($nilaiPerPenilaian[$i][$cpmk->id]) ? $nilaiPerPenilaian[$i][$cpmk->id] : null;
                                                                $nilaiInput = $nilaiCpmk ? $nilaiCpmk->nilai : 0;
                                                                $bobot = $cpmk->mks->where('id', $mk->id)->first()->pivot->bobot ?? 0;
                                                                $nilaiAkhir = ($nilaiInput * $bobot) / 100;
                                                                $minStandard = $minStandard ?? 55;
                                                            @endphp
                                                            <span class="{{ $nilaiInput < $minStandard ? 'below-min' : '' }}">
                                                                {{ number_format($nilaiAkhir, 0) }}
                                                            </span>
                                                        </td>
                                                    @endforeach
                                                    <td class="bg-light">
                                                        {{ number_format(app('App\Http\Controllers\PenilaianCpmkController')->calculateMkScore($mk->id, $mahasiswa->id, $i), 0) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Inisialisasi tab secara manual (sebagai fallback)
            $('#penilaianTab a').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
    </script>
@endsection