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
                <div class="table-responsive">
                    {{-- <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center align-middle">CPL</th>
                                <th class="text-center align-middle">Deskripsi CPL</th>
                                <th class="text-center align-middle">CPMK</th>
                                <th class="text-center align-middle">Deskripsi CPMK</th>
                                <th class="text-center align-middle">MK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cpls as $cpl)
                                @php
                                    // Hitung total baris untuk CPMK dan MK
                                    $filteredCpmks = $cpl->cpmks;
                                    $totalRows = $filteredCpmks->reduce(function ($carry, $cpmk) {
                                        return $carry + max($cpmk->mks->count(), 1);
                                    }, 0);
                                @endphp
                                <tr>
                                    <td rowspan="{{ $totalRows }}">{{ $cpl->kode_cpl }}</td>
                                    <td rowspan="{{ $totalRows }}">{{ $cpl->deskripsi }}</td>
                                    @foreach ($filteredCpmks as $cpmkIndex => $cpmk)
                                        @php
                                            $mkCount = $cpmk->mks->count();
                                        @endphp
                                        @if ($cpmkIndex > 0)
                                            <tr>
                                        @endif
                                        <td rowspan="{{ max($mkCount, 1) }}">{{ $cpmk->kode_cpmk }}</td>
                                        <td rowspan="{{ max($mkCount, 1) }}">{{ $cpmk->deskripsi }}</td>
                                        @if ($mkCount > 0)
                                            @foreach ($cpmk->mks as $mkIndex => $mk)
                                                @if ($mkIndex > 0)
                                                    <tr>
                                                @endif
                                                <td>{{ $mk->kode_mk }} - {{ $mk->deskripsi }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <td>-</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        
                        
                    </table> --}}
                    <form method="POST" action="{{ route('cpmk_cpl_mk.store') }}">
                        @csrf
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>CPL</th>
                                    <th>Deskripsi CPL</th>
                                    <th>CPMK</th>
                                    <th>Deskripsi CPMK</th>
                                    <th>MK</th>
                                    <th>Pilih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cpls as $cpl)
                                    @foreach ($cpl->cpmks as $cpmk)
                                        @foreach ($cpmk->mks as $mk)
                                        <tr>
                                            <td>{{ $cpl->kode_cpl }}</td>
                                            <td>{{ $cpl->deskripsi }}</td>
                                            <td>{{ $cpmk->kode_cpmk }}</td>
                                            <td>{{ $cpmk->deskripsi }}</td>
                                            <td>{{ $mk->kode_mk }} - {{ $mk->deskripsi }}</td>
                                            <td>
                                                <input type="checkbox" name="mappings[]" value="{{ $cpl->id }}|{{ $cpmk->id }}|{{ $mk->id }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        <button type="submit" class="btn btn-primary">Simpan Pemetaan</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
