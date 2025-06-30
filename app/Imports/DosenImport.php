<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class DosenImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        return new User([
            'name' => $row['name'],
            'nip' => $row['nip'],
            'password' => bcrypt($row['password']),
            'role' => 'dosen',
            'kode_prodi' => $this->kodeProdi,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nip' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'nip')->where(function ($query) {
                    return $query->where('kode_prodi', $this->kodeProdi);
                }),
            ],
            'password' => 'required|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Baris :row: Nama wajib diisi.',
            'name.max' => 'Baris :row: Nama terlalu panjang (maksimal 255 karakter).',
            'nip.required' => 'Baris :row: NIP wajib diisi.',
            'nip.unique' => 'Baris :row: NIP sudah digunakan untuk prodi ini.',
            'nip.max' => 'Baris :row: NIP terlalu panjang (maksimal 255 karakter).',
            'password.required' => 'Baris :row: Password wajib diisi.',
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

                $headerRow = $worksheet->rangeToArray('A1:C1', null, true, true, true)[1];
                $headings = array_map(function ($heading) {
                    return strtolower(str_replace(' ', '_', $heading));
                }, array_values($headerRow));
                $expectedHeadings = ['name', 'nip', 'password'];

                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template.');
                }
            },
        ];
    }
}