<?php

namespace App\Imports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;

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
        Log::info('Imported row data (raw): ', $row);
        $periode = trim($row['periode'] ?? '');
        $periodeValue = $periode !== '' ? $periode : null;
        $nipDosen = trim($row['nip_dosen'] ?? '');
        Log::info('nip_dosen before cleaning: ' . var_export($nipDosen, true));
        $nipDosen = ltrim($nipDosen, '`'); // Hapus tanda ` hanya dari awal
        Log::info('nip_dosen after cleaning: ' . var_export($nipDosen, true));

        // Jika nip_dosen tidak valid, simpan null atau nilai asli (opsional)
        if (empty($nipDosen)) {
            $nipDosen = null; // Atau biarkan kosong jika diizinkan
            Log::warning('nip_dosen is empty or invalid for row: ', $row);
        }

        return new Kelas([
            'kode_prodi' => $this->kodeProdi,
            'tahun_kurikulum' => $row['tahun_kurikulum'],
            'kode_mk' => $row['kode_mk'],
            'periode' => $periodeValue,
            'nip_dosen' => $nipDosen,
        ]);
    }

    public function rules(): array
    {
        return [
            'tahun_kurikulum' => 'required|date_format:Y',
            'kode_mk' => 'required|exists:mk,kode_mk',
            'periode' => 'nullable',
            'nip_dosen' => 'required', // Hapus validasi exists:users,nip
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tahun_kurikulum.required' => 'Baris :row: Kolom tahun kurikulum wajib diisi.',
            'tahun_kurikulum.date_format' => 'Baris :row: Format tahun kurikulum harus YYYY.',
            'kode_mk.required' => 'Baris :row: Kolom kode mata kuliah wajib diisi.',
            'kode_mk.exists' => 'Baris :row: Kode mata kuliah :input tidak valid. Periksa tabel mk untuk nilai yang benar.',
            'nip_dosen.required' => 'Baris :row: Kolom NIP dosen wajib diisi.',
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
        Log::warning('Import validation failure: ' . $message); // Log sebagai warning, bukan exception
        // Lepaskan exception untuk memungkinkan impor berlanjut
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
                $expectedHeadings = ['tahun_kurikulum', 'kode_mk', 'periode', 'nip_dosen'];

                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template. Harap gunakan header: tahun_kurikulum, kode_mk, periode, nip_dosen.');
                }
            },
        ];
    }
}