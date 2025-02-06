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
                <table id="pemetaanTable" class="table table-bordered table-hover">
                    <thead class="text-center">
                        <tr>
                            <th class="align-middle">CPL</th>
                            <th class="align-middle">Deskripsi CPL</th>
                            <th class="align-middle">Kode CPMK</th>
                            <th class="align-middle">Deskripsi CPMK</th>
                            <th class="align-middle">MK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cpls as $cpl)
                            @php
                                $firstCpl = true;
                                $totalCpmkRows = $cpl->cpmks->sum(fn($cpmk) => count($cpmk->mks) ?: 1);
                            @endphp
                            @foreach ($cpl->cpmks as $cpmk)
                                @php
                                    $firstCpmk = true;
                                    $totalMkRows = count($cpmk->mks) ?: 1;
                                @endphp
                                @foreach ($cpmk->mks as $index => $mk)
                                    <tr>
                                        @if ($firstCpl)
                                            <td rowspan="{{ $totalCpmkRows }}" class="align-middle text-center">{{ $cpl->kode_cpl }}</td>
                                            <td rowspan="{{ $totalCpmkRows }}" class="align-middle">{{ $cpl->deskripsi }}</td>
                                            @php $firstCpl = false; @endphp
                                        @endif
                                        
                                        @if ($firstCpmk)
                                            <td rowspan="{{ $totalMkRows }}" class="align-middle text-center">{{ $cpmk->kode_cpmk }}</td>
                                            <td rowspan="{{ $totalMkRows }}" class="align-middle">{{ $cpmk->deskripsi }}</td>
                                            @php $firstCpmk = false; @endphp
                                        @endif

                                        <td class="align-middle text-center">{{ $mk->kode_mk }}</td>
                                    </tr>
                                @endforeach

                                @if ($cpmk->mks->isEmpty())
                                    <tr>
                                        <td class="align-middle text-center">{{ $cpmk->kode_cpmk }}</td>
                                        <td class="align-middle">{{ $cpmk->deskripsi }}</td>
                                        <td class="align-middle text-center">-</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
