@extends('layouts.app')

@section('content')
    <style>
        /* General Styling */
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Styling Card */
        .card-custom {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
            padding: 20px;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #0052A2, #0080FF);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 1.6rem;
            font-weight: bold;
            border-radius: 15px 15px 0 0;
            letter-spacing: 1px;
        }

        .table-container {
            padding: 20px;
        }

        /* Table Styling */
        .custom-table {
            background: white;
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 0;
            overflow: hidden;
        }

        .custom-table thead th {
            background-color: #0052A2;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 15px;
            border-bottom: 2px solid #004080;
            vertical-align: middle; /* Menyelaraskan teks di tengah */
        }

        .custom-table thead tr th {
            border-right: 1px solid #e0e0e0;
        }

        .custom-table tbody td {
            padding: 12px;
            text-align: center;
            border-right: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
        }

        .custom-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .custom-table tbody tr:hover {
            background-color: #e9f2ff;
        }

        .custom-table td {
            vertical-align: middle;
        }

        /* Button Styling */
        .btn-save {
            background: linear-gradient(135deg, #0052A2, #0080FF);
            color: white;
            font-size: 1rem;
            font-weight: bold;
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #003580, #0052A2);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .custom-table th,
            .custom-table td {
                font-size: 0.85rem;
                padding: 10px;
            }

            .btn-save {
                width: 100%;
                font-size: 1rem;
                margin-top: 20px;
            }
        }
    </style>

    <div class="container mt-5">
        <div class="card card-custom">
            <!-- Header -->

            <!-- Table Content -->
            <div class="table-container">
                <!-- Tampilkan Notifikasi -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('pemetaan_CPMK-PL.update') }}" method="POST">
                    @csrf

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 5%;">No</th>
                                    <th rowspan="2" style="width: 20%;">Kode CPMK</th>
                                    <th colspan="{{ count($pl) }}">Profil Lulusan (PL)</th>
                                </tr>
                                <tr>
                                    @foreach ($pl as $ples)
                                        <th style="width: {{ 75 / count($pl) }}%;">{{ $ples->kode_pl }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cpmk as $index => $cpmkes)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $cpmkes->kode_cpmk }}</td>
                                        @foreach ($pl as $ples)
                                            <td>
                                                <input type="checkbox"
                                                    name="mapping[{{ $cpmkes->id }}][{{ $ples->id }}]"
                                                    value="1"
                                                    {{ $cpmkes->pl && $cpmkes->pl->contains($ples->id) ? 'checked' : '' }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>



                        </table>
                    </div>

                    <!-- Save Button -->
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-save">Simpan Pemetaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
