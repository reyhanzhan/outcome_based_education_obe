@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPMK - CPL - MK')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pemetaan CPMK - CPL - MK</h3>
            </div>
            <div class="card-body">
                <table id="pemetaanTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle text-center">No</th>
                            <th rowspan="2" class="align-middle text-center">CPL</th>
                            <th rowspan="2" class="align-middle text-center">Deskripsi CPL</th>
                            <th rowspan="2" class="align-middle text-center">Kode CPMK</th>
                            <th rowspan="2" class="align-middle text-center">CPMK</th>
                            <th rowspan="2" class="align-middle text-center">MK</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cpls as $index => $cpl)
                            @php
                                $totalCpmks = $cpl->cpmks->count();
                            @endphp

                            <tr>
                                <td rowspan="{{ $totalCpmks }}" class="text-center">{{ $index + 1 }}</td>
                                <td rowspan="{{ $totalCpmks }}">{{ $cpl->kode_cpl }}</td>
                                <td rowspan="{{ $totalCpmks }}">{{ $cpl->deskripsi }}</td>

                                @foreach ($cpl->cpmks as $cpmk)
                                    @php
                                        $totalMks = $cpmk->cpls->flatMap->mks->unique('id')->count();
                                    @endphp
                                    
                                    @if (!$loop->first) <tr> @endif
                                        <td rowspan="{{ $totalMks }}">{{ $cpmk->kode_cpmk }}</td>
                                        <td rowspan="{{ $totalMks }}">{{ $cpmk->deskripsi }}</td>

                                        @foreach ($cpl->mks as $mk)
                                            @if (!$loop->first) <tr> @endif
                                                <td>{{ $mk->kode_mk }}</td>
                                            </tr>
                                        @endforeach
                                @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
