<?php

namespace App\Imports;

use App\Models\Pl;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class PlImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        return new Pl([
            'kode_pl' => $row['kode_pl'],
            'deskripsi' => $row['deskripsi'],
            'kategori' => $row['kategori'],
            'kode_prodi' => $this->kodeProdi,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_pl' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('pl')->where(function ($query) {
                    return $query->where('kode_prodi', $this->kodeProdi);
                }),
            ],
            'deskripsi' => 'required|string',
            'kategori' => 'required|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode_pl.unique' => 'Baris :row: Kode PL sudah digunakan untuk prodi ini.',
            'kode_pl.required' => 'Baris :row: Kode PL wajib diisi.',
            'kode_pl.max' => 'Baris :row: Kode PL terlalu panjang (maksimal 255 karakter).',
            'deskripsi.required' => 'Baris :row: Deskripsi wajib diisi.',
            'kategori.required' => 'Baris :row: Kategori wajib diisi.',
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function onFailure(Failure ...$failures)
    {
        // Ambil hanya error pertama
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
                $expectedHeadings = ['kode_pl', 'deskripsi', 'kategori'];

                // Validasi header
                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template.');
                }
            },
        ];
    }
}