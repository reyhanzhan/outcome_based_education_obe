<?php

namespace App\Imports;

use App\Models\Krs;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;

class KrsImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        Log::info('Imported row data (raw): ', $row);
        $periode = trim($row['periode'] ?? '');
        $periodeValue = $periode !== '' ? $periode : null;
        $tahun = trim($row['tahun'] ?? '');
        $tahunValue = $tahun !== '' ? $tahun : null;
        $namaKelas = trim($row['nama_kelas'] ?? '');
        $nim = trim($row['nim'] ?? '');

        return new Krs([
            'kode_prodi' => $this->kodeProdi,
            'periode' => $periodeValue,
            'kode_mk' => $row['kode_mk'],
            'tahun' => $tahunValue,
            'nama_kelas' => $namaKelas,
            'nim' => $nim,
        ]);
    }

    public function rules(): array
    {
        return [
            'periode' => 'required',
            'kode_mk' => 'required|exists:mk,kode_mk',
            'tahun' => 'required|date_format:Y',
            'nama_kelas' => 'required',
            'nim' => 'required', // Hapus validasi exists:mahasiswa,nim untuk fleksibilitas
        ];
    }

    public function customValidationMessages()
    {
        return [
            'periode.required' => 'Baris :row: Kolom periode wajib diisi.',
            'kode_mk.required' => 'Baris :row: Kolom kode mata kuliah wajib diisi.',
            'kode_mk.exists' => 'Baris :row: Kode mata kuliah :input tidak valid. Periksa tabel mk untuk nilai yang benar.',
            'tahun.required' => 'Baris :row: Kolom tahun wajib diisi.',
            'tahun.date_format' => 'Baris :row: Format tahun harus YYYY.',
            'nama_kelas.required' => 'Baris :row: Kolom nama kelas wajib diisi.',
            'nim.required' => 'Baris :row: Kolom NIM wajib diisi.',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function onFailure(Failure ...$failures)
    {
        $failure = $failures[0];
        $message = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        Log::warning('Import validation failure: ' . $message); // Log sebagai warning
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $worksheet = $event->reader->getDelegate()->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();

                if ($highestRow < 2) {
                    throw new \Exception('File Excel kosong.');
                }

                $headerRow = $worksheet->rangeToArray('A1:E1', null, true, true, true)[1];
                $headings = array_map(function ($heading) {
                    return strtolower(str_replace(' ', '_', $heading));
                }, array_values($headerRow));
                $expectedHeadings = ['periode', 'kode_mk', 'tahun', 'nama_kelas', 'nim'];

                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template. Harap gunakan header: periode, kode_mk, tahun, nama_kelas, nim.');
                }
            },
        ];
    }
}