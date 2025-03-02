@extends('layouts_adminlte.app')

@section('title', 'Input Nilai Mahasiswa')

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

        .bobot-highlight {
            background-color: #e9ecef; /* Abu-abu muda untuk bobot */
            font-weight: bold;
            color: #000000; /* Hitam untuk kontras */
        }

        .success-bg {
            background-color: #d4edda; /* Hijau muda untuk sukses */
            color: #155724; /* Hijau gelap untuk teks sukses */
        }

        .error-bg {
            background-color: #f8d7da; /* Merah muda untuk error */
            color: #721c24; /* Merah gelap untuk teks error */
        }

        .input-group .form-control {
            border-color: #007bff; /* Biru untuk border input */
        }

        .input-group .form-control.below-min {
            border-color: #dc3545 !important; /* Merah untuk nilai di bawah standar minimum */
            background-color: #fff3cd; /* Kuning muda untuk peringatan */
        }

        .min-standard {
            color: #6c757d; /* Abu-abu untuk standar minimum */
            font-style: italic;
        }

        .btn-primary {
            background-color: #007bff; /* Biru primer AdminLTE */
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3; /* Biru gelap saat hover */
            border-color: #0056b3;
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
                    <h3 class="card-title">Input Nilai Mahasiswa untuk {{ $mk->kode_mk }} - {{ $mk->deskripsi }}</h3>
                    <a href="{{ route('nilai.mahasiswa.choose') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Pilih Mata Kuliah</a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success success-bg">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger error-bg">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('nilai.mahasiswa.store') }}" method="POST" id="nilaiForm">
                        @csrf
                        <input type="hidden" name="mk_id" value="{{ $mk->id }}">

                        <!-- Form untuk mengatur standar minimum -->
                        <div class="form-group mb-3">
                            <label for="minStandard">Standar Minimum Nilai CPMK:</label>
                            <select name="min_standard" id="minStandard" class="form-control">
                                <option value="55" @if(!isset($minStandard) || $minStandard == 55) selected @endif>55</option>
                                <option value="60" @if(isset($minStandard) && $minStandard == 60) selected @endif>60</option>
                                <option value="65" @if(isset($minStandard) && $minStandard == 65) selected @endif>65</option>
                                <option value="70" @if(isset($minStandard) && $minStandard == 70) selected @endif>70</option>
                                <option value="75" @if(isset($minStandard) && $minStandard == 75) selected @endif>75</option>
                            </select>
                            <small class="form-text text-muted">Pilih standar minimum untuk semua CPMK di MK ini (hanya untuk visualisasi di grafik radar).</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="bg-light" style="width: 15%;">Nama Mahasiswa</th>
                                        @foreach ($cpmks as $cpmk)
                                            <th class="bobot-highlight">{{ $cpmk->kode_cpmk }} (Bobot: {{ $cpmk->mks()->where('mk_id', $mk->id)->first()->pivot->bobot ?? 0 }}, Min: <span class="min-standard">{{ $minStandard ?? 55 }}</span>)</th>
                                        @endforeach
                                        <th class="bg-light">Nilai Total MK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mahasiswas as $mahasiswa)
                                        <tr>
                                            <td class="bg-light">{{ $mahasiswa->nama }}</td>
                                            @foreach ($cpmks as $cpmk)
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" name="nilai_{{ $mahasiswa->id }}_{{ $cpmk->id }}" class="form-control nilai-input" data-min="{{ $minStandard ?? 55 }}" data-bobot="{{ $cpmk->mks()->where('mk_id', $mk->id)->first()->pivot->bobot ?? 0 }}" value="{{ number_format($mahasiswa->nilaiCpmks()->where('mk_id', $mk->id)->where('cpmk_id', $cpmk->id)->first()->nilai ?? 0, 0) }}" min="0" max="100" step="0.01">
                                                    </div>
                                                </td>
                                            @endforeach
                                            <td class="bg-light">
                                                {{ number_format($mk->calculateMkScore($mahasiswa->id), 0) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="submit" class="btn btn-primary">Simpan Semua Nilai</button>
                        </div>
                    </form>
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
            $('.nilai-input').on('input', function() {
                let value = parseFloat($(this).val()) || 0;
                let min = parseInt($(this).data('min')); // Standar minimum
                let bobot = parseFloat($(this).data('bobot')) || 0; // Bobot CPMK-MK
                let nilaiInput = value; // Nilai input sebelum bobot

                if (value < 0) {
                    $(this).val(0); // Batasi minimal 0
                    toastr.warning('Nilai minimal adalah 0%.');
                } else if (value > 100) {
                    $(this).val(100); // Batasi maksimal 100%
                    toastr.warning('Nilai maksimal adalah 100%.');
                }

                // Tandai input dengan warna merah jika nilai input < standar minimum
                if (nilaiInput < min) {
                    $(this).addClass('below-min');
                } else {
                    $(this).removeClass('below-min');
                }

                // Update nilai total MK secara real-time
                updateMkTotal();
            });

            function updateMkTotal() {
                $('tr').each(function() {
                    let mahasiswaId = $(this).find('td:first').data('mahasiswa-id');
                    if (mahasiswaId) {
                        let total = 0;
                        let bobotTotal = 0;
                        @foreach ($cpmks as $cpmk)
                            let bobot_{{ $cpmk->id }} = {{ $cpmk->mks()->where('mk_id', $mk->id)->first()->pivot->bobot ?? 0 }};
                            let nilai_{{ $cpmk->id }} = parseFloat($(`input[name="nilai_${mahasiswaId}_${{ $cpmk->id }}"]`).val()) || 0;
                            total += (nilai_{{ $cpmk->id }} * bobot_{{ $cpmk->id }} / 100);
                            bobotTotal += bobot_{{ $cpmk->id }};
                        @endforeach
                        $(this).find('td.bg-light:last').text((bobotTotal > 0 ? (total / (bobotTotal / 100)).toFixed(0) : 0));
                    }
                });
            }

            $('#nilaiForm').on('submit', function(e) {
                console.log('Form submitted:', $(this).serialize());
                return true; // Pastikan form dikirim
            });
        });
    }
</script>
@endsection