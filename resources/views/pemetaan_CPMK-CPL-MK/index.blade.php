@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPL - CPMK - MK')

@section('css')
<style>
    .card-header.bg-primary {
        background-color: #007bff !important;
        color: #fff;
    }

    .table-bordered th, .table-bordered td {
        border: 2px solid #dee2e6 !important;
        vertical-align: middle;
        text-align: center;
        padding: 12px;
        font-size: 0.9rem;
        color: #000;
    }

    .table-bordered thead th {
        background-color: #e9ecef;
        color: #000;
        font-weight: bold;
    }

    .table-bordered tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .table-bordered tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }

    .rowspan-header {
        /* font-weight: bold;
        color: #000; */
    }

    .table-responsive {
        overflow-x: auto;
    }

    .no-mk {
        color: #6c757d;
        font-style: italic;
    }

    @media (max-width: 768px) {
        .table-bordered th, .table-bordered td {
            font-size: 0.8rem;
            padding: 8px;
        }

        .table-bordered th {
            white-space: nowrap;
        }
    }
</style>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Pemetaan CPL - CPMK - MK - {{ Auth::user()->programStudi->nama_prodi ?? 'Prodi Tidak Ditemukan' }}</h3>
            </div>
            <div class="card-body">
                @if ($cpls->isEmpty())
                    <div class="alert alert-warning">
                        Tidak ada data CPL, CPMK, atau MK untuk ditampilkan. Silakan tambahkan data terlebih dahulu.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
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
                                        $filteredCpmks = $cpl->cpmks;
                                        $totalRows = $filteredCpmks->reduce(function ($carry, $cpmk) {
                                            return $carry + max($cpmk->mks->count(), 1);
                                        }, 0);
                                    @endphp
                                    <tr>
                                        <td rowspan="{{ $totalRows }}" class="rowspan-header" style="vertical-align: middle;">{{ $cpl->kode_cpl }}</td>
                                        <td rowspan="{{ $totalRows }}" class="rowspan-header" style="vertical-align: middle;">{{ $cpl->deskripsi }}</td>
                                        @foreach ($filteredCpmks as $cpmkIndex => $cpmk)
                                            @php
                                                $mkCount = $cpmk->mks->count();
                                            @endphp
                                            @if ($cpmkIndex > 0)
                                    <tr>
                                @endif
                                <td rowspan="{{ max($mkCount, 1) }}" class="rowspan-header" style="vertical-align: middle;">{{ $cpmk->kode_cpmk }}</td>
                                <td rowspan="{{ max($mkCount, 1) }}" class="rowspan-header" style="vertical-align: middle;">{{ $cpmk->deskripsi }}</td>
                                @if ($mkCount > 0)
                                    @foreach ($cpmk->mks as $mkIndex => $mk)
                                        @if ($mkIndex > 0)
                                            <tr>
                                        @endif
                                        <td>{{ $mk->kode_mk }} - {{ $mk->deskripsi }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <td class="no-mk">-</td>
                                    </tr>
                                @endif
                                @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
        $(".table-responsive").on("scroll", function() {
            var scrollTop = $(this).scrollTop();
            $(".rowspan-header").css("top", scrollTop + "px");
        });
    });
</script>

@if (session('success'))
    <script>
        toastr.success('{{ session('success') }}', "Sukses", { position: 'top-right', timeOut: 5000 });
    </script>
@endif
@if (session('error'))
    <script>
        toastr.error('{{ session('error') }}', "Error", { position: 'top-right', timeOut: 5000 });
    </script>
@endif
@endsection