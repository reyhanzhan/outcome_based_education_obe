<?php

namespace App\Imports;

use App\Models\Cpmk;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class CpmkImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        return new Cpmk([
            'kode_cpmk' => $row['kode_cpmk'],
            'deskripsi' => $row['deskripsi'],
            'kode_prodi' => $this->kodeProdi,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_cpmk' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('cpmk')->where(function ($query) {
                    return $query->where('kode_prodi', $this->kodeProdi);
                }),
            ],
            'deskripsi' => 'required|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode_cpmk.unique' => 'Baris :row: Kode CPMK sudah digunakan untuk prodi ini.',
            'kode_cpmk.required' => 'Baris :row: Kode CPMK wajib diisi.',
            'kode_cpmk.max' => 'Baris :row: Kode CPMK terlalu panjang (maksimal 255 karakter).',
            'deskripsi.required' => 'Baris :row: Deskripsi wajib diisi.',
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

                $headerRow = $worksheet->rangeToArray('A1:B1', null, true, true, true)[1];
                $headings = array_map(function ($heading) {
                    return strtolower(str_replace(' ', '_', $heading));
                }, array_values($headerRow));
                $expectedHeadings = ['kode_cpmk', 'deskripsi'];

                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template.');
                }
            },
        ];
    }
}