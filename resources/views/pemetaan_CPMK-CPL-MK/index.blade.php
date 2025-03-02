@extends('layouts_adminlte.app')

@section('title', 'Pemetaan CPL - CPMK - MK')

@section('css')
<style>
    .card-header.bg-primary {
        background-color: #007bff !important;
        color: #fff;
    }

    .table-bordered th, .table-bordered td {
        border: 2px solid #dee2e6 !important; /* Garis tabel lebih tebal dan kontras */
        vertical-align: middle;
        text-align: center;
        padding: 12px; /* Padding lebih besar untuk kejelasan */
        font-size: 0.9rem; /* Ukuran font sedikit lebih kecil untuk tabel besar */
        color: #000; /* Warna teks hitam di semua sel */
    }

    .table-bordered thead th {
        background-color: #e9ecef; /* Latar belakang abu-abu muda untuk header */
        color: #000; /* Warna teks hitam di header */
        font-weight: bold;
    }

    .table-bordered tbody tr:nth-child(even) {
        background-color: #f8f9fa; /* Warna abu-abu sangat muda untuk baris genap */
    }

    .table-bordered tbody tr:nth-child(odd) {
        background-color: #ffffff; /* Warna putih untuk baris ganjil */
    }

    .rowspan-header {
       
        /* font-weight: bold;
        color: #000; */
    }

    .table-responsive {
        overflow-x: auto; /* Memastikan tabel scroll horizontal jika terlalu lebar */
    }

    .no-mk {
        color: #6c757d; /* Warna abu-abu untuk data kosong */
        font-style: italic;
    }

    /* Responsive untuk perangkat kecil */
    @media (max-width: 768px) {
        .table-bordered th, .table-bordered td {
            font-size: 0.8rem; /* Ukuran font lebih kecil di perangkat kecil */
            padding: 8px; /* Padding lebih kecil di perangkat kecil */
        }

        .table-bordered th {
            white-space: nowrap; /* Hindari patah baris di header */
        }
    }
</style>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pemetaan CPL - CPMK - MK</h3>
                </div>
                <div class="card-body">
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
                                        // Hitung total baris untuk CPMK dan MK
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
@endsection