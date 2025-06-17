<?php

namespace App\Imports;

use App\Models\Kurikulum;
use App\Models\Mk;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;

class KurikulumImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        // Debugging: Log data mentah
        Log::info('Raw imported row data: ', $row);

        // Bersihkan dan konversi semester
        $semesterValue = trim($row['semester'] ?? '');
        $semester = $semesterValue === '' || $semesterValue === null ? null : filter_var($semesterValue, FILTER_VALIDATE_INT);

        if ($semester === false) {
            Log::warning('Invalid semester value detected: ' . $semesterValue);
            $semester = null; // Atau lempar exception jika ingin strict
        }

        return new Kurikulum([
            'tahun' => $row['tahun'],
            'kode_prodi' => $this->kodeProdi,
            'kode_mk' => $row['kode_mk'],
            'semester' => $semester,
        ]);
    }

    public function rules(): array
    {
        return [
            'tahun' => 'required|date_format:Y',
            'kode_mk' => 'required|exists:mk,kode_mk',
            'semester' => 'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tahun.required' => 'Baris :row: Kolom tahun wajib diisi.',
            'tahun.date_format' => 'Baris :row: Format tahun harus YYYY.',
            'kode_mk.required' => 'Baris :row: Kolom kode mata kuliah wajib diisi.',
            'kode_mk.exists' => 'Baris :row: Kode mata kuliah tidak valid.',
           
        ];
    }

    public function startRow(): int
    {
        return 2; // Mulai dari baris kedua (baris pertama untuk header)
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
                $headerRow = $worksheet->rangeToArray('A1:C1', null, true, true, true)[1];
                $headings = array_map(function ($heading) {
                    return strtolower(str_replace(' ', '_', $heading));
                }, array_values($headerRow));
                $expectedHeadings = ['tahun', 'kode_mk', 'semester'];

                // Validasi header
                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template. Harap gunakan header: tahun, kode_mk, semester.');
                }
            },
        ];
    }
}