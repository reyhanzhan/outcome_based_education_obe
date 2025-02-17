@extends('layouts_adminlte.app')

@section('title', 'Pemetaan MK - CPMK - Sub CPMK')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Pemetaan MK - CPMK - Sub CPMK</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>MK</th>
                                <th>CPMK</th>
                                <th>Deskripsi CPMK</th>
                                <th>Sub-CPMK</th>
                                <th>Uraian Sub-CPMK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mks as $mk)
                                @foreach ($mk->cpmks as $cpmk)
                                    @foreach ($cpmk->subcpmks as $subcpmk)
                                        <tr>
                                            <td>{{ $mk->kode_mk }}</td>
                                            <td>{{ $cpmk->kode_cpmk }}</td>
                                            <td>{{ $cpmk->deskripsi }}</td>
                                            <td>{{ $subcpmk->kode_subcpmk }}</td>
                                            <td>{{ $subcpmk->uraian }}</td>
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
