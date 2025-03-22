@extends('layouts_adminlte.app')

@section('title', 'Input Nilai Mahasiswa')

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

    .success-bg {
        background-color: #d4edda;
        color: #155724;
    }

    .error-bg {
        background-color: #f8d7da;
        color: #721c24;
    }

    .input-group .form-control {
        border-color: #007bff;
    }

    .input-group .form-control.below-min {
        border-color: #dc3545 !important;
        background-color: #fff3cd;
    }

    .min-standard {
        color: #6c757d;
        font-style: italic;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #5a6268;
    }
</style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Input Nilai Mahasiswa: {{ $mahasiswa->nama }} ({{ $mahasiswa->nim }}) untuk {{ $mk->kode_mk }} - {{ $mk->deskripsi }}</h3>
                    <a href="{{ route('nilai.mahasiswa.choose_mata_kuliah') }}?periode={{ session('previous_periode') }}&kelas={{ session('previous_kelas') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali </a>
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
                        <input type="hidden" name="nim" value="{{ $mahasiswa->nim }}">
                        <input type="hidden" name="mk_id" value="{{ $mk->id }}">

                        <!-- Form untuk mengatur standar minimum -->
                        <div class="form-group mb-3">
                            <label for="minStandard">Standar Minimum Nilai CPMK:</label>
                            <select name="min_standard" id="minStandard" class="form-control">
                                <option value="55" @if($minStandard == 55) selected @endif>55</option>
                                <option value="60" @if($minStandard == 60) selected @endif>60</option>
                                <option value="65" @if($minStandard == 65) selected @endif>65</option>
                                <option value="70" @if($minStandard == 70) selected @endif>70</option>
                                <option value="75" @if($minStandard == 75) selected @endif>75</option>
                            </select>
                            <small class="form-text text-muted">Pilih standar minimum untuk semua CPMK di MK ini (akan disimpan ke database).</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="bg-light" style="width: 15%;">Nama Mahasiswa</th>
                                        @foreach ($cpmks as $cpmk)
                                            <th class="bobot-highlight">{{ $cpmk->kode_cpmk }} (Bobot: {{ $cpmk->pivot->bobot ?? 0 }}%)</th>
                                        @endforeach
                                        {{-- <th class="bg-light">Nilai Total MK</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="bg-light">{{ $mahasiswa->nama }}</td>
                                        @foreach ($cpmks as $cpmk)
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="nilai_{{ $mahasiswa->id }}_{{ $cpmk->id }}" 
                                                           class="form-control nilai-input" 
                                                           data-min="{{ $minStandard }}" 
                                                           data-bobot="{{ $cpmk->pivot->bobot ?? 0 }}" 
                                                           value="{{ number_format(old('nilai_' . $mahasiswa->id . '_' . $cpmk->id, $cpmk->nilaiCpmks->firstWhere('cpmk_id', $cpmk->id)->nilai ?? 0), 0) }}" 
                                                           min="0" max="100" step="0.01">
                                                </div>
                                            </td>
                                        @endforeach
                                        {{-- <td class="bg-light total-mk">
                                            {{ number_format($mk->calculateMkScore($mahasiswa->nim), 0) }}
                                        </td> --}}
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.nilai-input').on('input', function() {
            let value = parseFloat($(this).val()) || 0;
            let min = parseInt($(this).data('min'));
            let bobot = parseFloat($(this).data('bobot')) || 0;

            if (value < 0) {
                $(this).val(0);
                toastr.warning('Nilai minimal adalah 0%.');
            } else if (value > 100) {
                $(this).val(100);
                toastr.warning('Nilai maksimal adalah 100%.');
            }

            if (value < min) {
                $(this).addClass('below-min');
            } else {
                $(this).removeClass('below-min');
            }

            // Update nilai total MK secara real-time
            let total = 0;
            let bobotTotal = 0;
            @foreach ($cpmks as $cpmk)
                let bobot_{{ $cpmk->id }} = {{ $cpmk->pivot->bobot ?? 0 }};
                let nilai_{{ $cpmk->id }} = parseFloat($(`input[name="nilai_{{ $mahasiswa->id }}_{{ $cpmk->id }}"]`).val()) || 0;
                total += (nilai_{{ $cpmk->id }} * bobot_{{ $cpmk->id }} / 100);
                bobotTotal += bobot_{{ $cpmk->id }};
            @endforeach
            $('.total-mk').text((bobotTotal > 0 ? (total / (bobotTotal / 100)).toFixed(0) : 0));
        });

        $('#nilaiForm').on('submit', function(e) {
            console.log('Form submitted:', $(this).serialize());
            return true;
        });
    });
</script>
@endsection