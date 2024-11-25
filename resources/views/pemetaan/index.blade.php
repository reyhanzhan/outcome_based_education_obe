@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4">Pemetaan CPL ke PL</h1>

        <form action="{{ route('pemetaan.update') }}" method="POST">
            @csrf
            @method('POST')

            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th rowspan="2" style="width: 5%;">No</th>
                            <th rowspan="2" style="width: 20%;">Kode CPL</th>
                            <th colspan="{{ count($pl) }}">Profil Lulusan (PL)</th>
                        </tr>
                        <tr>
                            @foreach ($pl as $ples)
                                <th style="width: {{ 75 / count($pl) }}%;">{{ $ples->kode_pl }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cpl as $index => $cples)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $cples->kode_cpl }}</td>
                                @foreach ($pl as $ples)
                                    <td>
                                        <input type="checkbox" name="mapping[{{ $cples->id }}][{{ $ples->id }}]"
                                            value="1"
                                            {{ $cples->pl && $cples->pl->contains($ples->id) ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-success mt-3">Simpan Pemetaan</button>
        </form>
    </div>
@endsection


<style>
    .table th,
    .table td {
        vertical-align: middle;
        text-align: center;
    }
</style>
