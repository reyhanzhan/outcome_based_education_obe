<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class MahasiswaImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        // Pastikan nilai diubah menjadi string sebelum disimpan
        return new Mahasiswa([
            'nim' => $row['nim'],
            'nama' => (string)$row['nama'],
            'periode_masuk' => $row['periode_masuk'],
            'sistem_kuliah' => (string)$row['sistem_kuliah'],
            'jalur_penerimaan' => (string)$row['jalur_penerimaan'],
            'gelombang_daftar' => (string)$row['gelombang_daftar'],
            'agama' => (string)$row['agama'],
            'kode_prodi' => $this->kodeProdi,
        ]);
    }

    public function rules(): array
    {
        return [
            'nim' => [
                'required',
                'alpha_num',
                \Illuminate\Validation\Rule::unique('mahasiswa')->where(function ($query) {
                    return $query->where('kode_prodi', $this->kodeProdi);
                }),
            ],
            'nama' => 'required|string|max:100',
            'periode_masuk' => 'required',
            'sistem_kuliah' => 'required|string|in:Reguler Pagi,Reguler Sore',
            'jalur_penerimaan' => 'required|string',
            'gelombang_daftar' => 'required|string',
            'agama' => 'required|string|in:Islam,Kristen,Hindu,Buddha,Khonghucu',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nim.unique' => 'Baris :row: NIM sudah digunakan untuk prodi ini.',
            'nim.required' => 'Baris :row: NIM wajib diisi.',
            'nim.alpha_num' => 'Baris :row: NIM hanya boleh berisi huruf dan angka.',
            'nim.size' => 'Baris :row: NIM harus 10 karakter.',
            'nama.required' => 'Baris :row: Nama wajib diisi.',
            'nama.max' => 'Baris :row: Nama terlalu panjang (maksimal 100 karakter).',
            'periode_masuk.required' => 'Baris :row: Periode Masuk wajib diisi.',
            'periode_masuk.max' => 'Baris :row: Periode Masuk terlalu panjang (maksimal 9 karakter).',
            'sistem_kuliah.required' => 'Baris :row: Sistem Kuliah wajib diisi.',
            'sistem_kuliah.in' => 'Baris :row: Sistem Kuliah harus Reguler Pagi atau Reguler Sore.',
            'jalur_penerimaan.required' => 'Baris :row: Jalur Penerimaan wajib diisi.',
            'gelombang_daftar.required' => 'Baris :row: Gelombang Daftar wajib diisi.',
            'agama.required' => 'Baris :row: Agama wajib diisi.',
            'agama.in' => 'Baris :row: Agama harus salah satu dari: Islam, Kristen, Hindu, Buddha, Khonghucu.',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function onFailure(Failure ...$failures)
    {
        // Ambil semua error untuk setiap kegagalan
        $failure = $failures[0];
        $message = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        throw new \Exception($message);
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $worksheet = $event->reader->getDelegate()->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();

                // Periksa apakah file kosong
                if ($highestRow < 2) {
                    throw new \Exception('File Excel kosong.');
                }

                // Ambil header dari baris pertama
                $headerRow = $worksheet->rangeToArray('A1:G1', null, true, true, true)[1];
                $headings = array_map(function ($heading) {
                    return strtolower(str_replace(' ', '_', $heading));
                }, array_values($headerRow));
                $expectedHeadings = ['nim', 'nama', 'periode_masuk', 'sistem_kuliah', 'jalur_penerimaan', 'gelombang_daftar', 'agama'];

                // Validasi header
                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template. Harap gunakan header: nim, nama, periode_masuk, sistem_kuliah, jalur_penerimaan, gelombang_daftar, agama.');
                }
            },
        ];
    }
}