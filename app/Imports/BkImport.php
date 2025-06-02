<?php

namespace App\Imports;

use App\Models\Bk;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class BkImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        return new Bk([
            'kode_bk' => $row['kode_bk'],
            'deskripsi' => $row['deskripsi'], // Ubah dari 'nama_bk' ke 'deskripsi'
            'kode_prodi' => $this->kodeProdi,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_bk' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('bk')->where(function ($query) {
                    return $query->where('kode_prodi', $this->kodeProdi);
                }),
            ],
            'deskripsi' => 'required|string', // Ubah dari 'nama_bk' ke 'deskripsi'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode_bk.unique' => 'Baris :row: Kode BK sudah digunakan untuk prodi ini.',
            'kode_bk.required' => 'Baris :row: Kode BK wajib diisi.',
            'kode_bk.max' => 'Baris :row: Kode BK terlalu panjang (maksimal 255 karakter).',
            'deskripsi.required' => 'Baris :row: Deskripsi wajib diisi.', // Ubah pesan
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
                $expectedHeadings = ['kode_bk', 'deskripsi']; // Ubah dari 'nama_bk' ke 'deskripsi'

                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template.');
                }
            },
        ];
    }
}