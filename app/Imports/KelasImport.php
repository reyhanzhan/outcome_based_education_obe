<?php

namespace App\Imports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class KelasImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        return new Kelas([
            'tahun' => $row['tahun'],
            'kode_mk' => $row['kode_mk'],
            'periode' => $row['periode'],
            'nip_dosen' => $row['nip_dosen'],
            'kode_prodi' => $this->kodeProdi,
        ]);
    }

    public function rules(): array
    {
        return [
            'tahun' => 'required|numeric|min:2000|max:2025',
            'kode_mk' => 'required|exists:mk,kode_mk',
            'periode' => 'nullable',
            'nip_dosen' => 'required|exists:users,nip',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tahun_kurikulum.required' => 'Baris :row: Kolom tahun kurikulum wajib diisi.',
            'tahun_kurikulum.numeric' => 'Baris :row: Tahun kurikulum harus berupa angka.',
            'tahun_kurikulum.min' => 'Baris :row: Tahun kurikulum harus minimal 2000.',
            'tahun_kurikulum.max' => 'Baris :row: Tahun kurikulum tidak boleh lebih dari 2025.',
            'kode_mk.required' => 'Baris :row: Kolom kode mata kuliah wajib diisi.',
            'kode_mk.exists' => 'Baris :row: Kode mata kuliah :input tidak valid. Periksa tabel mk untuk nilai yang benar.',
            'nip_dosen.required' => 'Baris :row: Kolom NIP dosen wajib diisi.',
            'nip_dosen.exists' => 'Baris :row: NIP dosen :input tidak valid. Periksa tabel users untuk nilai yang benar.',
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
        throw new \Exception($message);
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

                $headerRow = $worksheet->rangeToArray('A1:D1', null, true, true, true)[1];
                $headings = array_map(function ($heading) {
                    return strtolower(str_replace(' ', '_', $heading));
                }, array_values($headerRow));
                $expectedHeadings = ['tahun', 'kode_mk', 'periode', 'nip_dosen'];

                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template.');
                }
            },
        ];
    }
}