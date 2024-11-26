@extends('layouts.app')
@section('title', 'Daftar Bahan Kajian')
@section('content')
    <style>
        .custom-table thead {
            color: #ffffff !important;
            background-color: #0052A2 !important;
        }

        .custom-table thead th {
            color: #ffffff !important;
            text-align: center;
            padding: 1rem;
        }
    </style>



    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="form-card">
            <h2 class="text-primary text-center mb-4">Edit BK</h2>

            <form action="{{ route('bk.update', $bk->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="kode_bk" class="form-label">Kode BK</label>
                    <input type="text" id="kode_bk" name="kode_bk" class="custom-input" value="{{ $bk->kode_bk }}" required>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="custom-input" rows="3" required>{{ $bk->deskripsi }}</textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('bk.index') }}" class="custom-btn custom-btn-blue">Kembali</a>
                    <button type="submit" class="custom-btn custom-btn-green">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

<style>
    .min-vh-100 {
        min-height: 100vh;
    }

    .form-card {
        max-width: 600px;
        width: 100%;
        background: linear-gradient(145deg, #f8f9fa, #e9ecef);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
    }

    .text-primary {
        color: #357ab8;
        font-weight: 700;
    }

    .custom-input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        background-color: #fdfdfd;
        transition: border 0.3s, box-shadow 0.3s;
    }

    .custom-input:focus {
        border-color: #357ab8;
        box-shadow: 0 0 8px rgba(53, 122, 184, 0.25);
    }

    .custom-btn {
        font-size: 1.1rem;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: background 0.3s, transform 0.2s;
    }

    .custom-btn-blue {
        background-color: #357ab8;
        color: #ffffff;
    }

    .custom-btn-blue:hover {
        background-color: #28527a;
    }

    .custom-btn-green {
        background-color: #28a745;
        color: #ffffff;
    }

    .custom-btn-green:hover {
        background-color: #218838;
    }

    .custom-btn:active {
        transform: translateY(1px);
    }


</style>
