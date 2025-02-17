@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPL - CPMK - MK')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pemetaan CPL - CPMK - MK</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('teknik_penilaian.store') }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>CPL</th>
                                        <th>MK</th>
                                        <th>CPMK</th>
                                        <th>Partisipasi</th>
                                        <th>Observasi</th>
                                        <th>Unjuk Kerja</th>
                                        <th>UTS</th>
                                        <th>UAS</th>
                                        <th>Tes Lisan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pemetaan as $item)
                                        <tr>
                                            <td>{{ $item->kode_cpl }}</td>
                                            <td>{{ $item->kode_mk }}</td>
                                            <td>{{ $item->kode_cpmk }}</td>
                                            <td class="text-center">
                                                <input type="checkbox" name="pemetaan[{{ $item->id }}][partisipasi]"
                                                    {{ $item->partisipasi ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="pemetaan[{{ $item->id }}][observasi]"
                                                    {{ $item->observasi ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="pemetaan[{{ $item->id }}][unjuk_kerja]"
                                                    {{ $item->unjuk_kerja ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="pemetaan[{{ $item->id }}][tes_tulis_uts]"
                                                    {{ $item->tes_tulis_uts ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="pemetaan[{{ $item->id }}][tes_tulis_uas]"
                                                    {{ $item->tes_tulis_uas ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="pemetaan[{{ $item->id }}][tes_lisan]"
                                                    {{ $item->tes_lisan ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary mt-3">Simpan</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
@endsection
