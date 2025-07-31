<?php

namespace App\Imports;

use App\Models\Krs;
use App\Models\Kurikulum;
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
    private $tahunFilter;

    public function __construct($kodeProdi, $tahunFilter = null)
    {
        $this->kodeProdi = $kodeProdi;
        $this->tahunFilter = $tahunFilter;
        Log::info("KrsImport initialized with kodeProdi: {$this->kodeProdi}, tahunFilter: {$this->tahunFilter}");
    }

    public function model(array $row)
    {
        Log::debug('Processing row (raw): ' . json_encode($row));

        $periode = trim($row['periode'] ?? '');
        $periodeValue = $periode !== '' ? $periode : null;
        $tahun = trim($row['tahun'] ?? '');
        $tahunValue = $tahun !== '' ? $tahun : null;
        $namaKelas = trim($row['nama_kelas'] ?? '');
        $nim = trim($row['nim'] ?? '');

        // Validasi awal sebelum filter tahun
        if (empty($periode) || empty($tahun) || empty($namaKelas) || empty($nim) || empty($row['kode_mk'])) {
            Log::warning("Skipping row due to empty required fields, row: " . json_encode($row));
            return null;
        }

        // Cari atau buat kurikulum_id berdasarkan tahun dan kode_prodi
        $kurikulumId = null;
        if ($tahunValue) {
            $kurikulum = Kurikulum::firstOrCreate(
                ['kode_prodi' => $this->kodeProdi, 'tahun' => $tahunValue],
                ['kode_mk' => 'MK001', 'semester' => 1]
            );
            $kurikulumId = $kurikulum->id;
            Log::info("Kurikulum found/created with id: {$kurikulumId} for tahun: {$tahunValue}");
        }

        $krs = new Krs([
            'kode_prodi' => $this->kodeProdi,
            'kurikulum_id' => $kurikulumId,
            'periode' => $periodeValue,
            'kode_mk' => $row['kode_mk'],
            'tahun' => $tahunValue,
            'nama_kelas' => $namaKelas,
            'nim' => $nim,
        ]);

        Log::info("KRS data prepared for save: " . json_encode($krs->toArray()));
        return $krs;
    }

    public function rules(): array
    {
        return [
            'periode' => 'required',
            'kode_mk' => 'required|exists:mk,kode_mk',
            'tahun' => 'required|date_format:Y',
            'nama_kelas' => 'required',
            'nim' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'periode.required' => 'Baris :row: Kolom periode wajib diisi.',
            'kode_mk.required' => 'Baris :row: Kolom kode mata kuliah wajib diisi.',
            'kode_mk.exists' => 'Baris :row: Kode mata kuliah :input tidak valid.',
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
        Log::error('Import validation failure: ' . $message);
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

                $headerRow = $worksheet->rangeToArray('A1:E1', null, true, true, true)[1];
                $headings = array_map(function ($heading) {
                    return strtolower(str_replace(' ', '_', $heading));
                }, array_values($headerRow));
                $expectedHeadings = ['periode', 'kode_mk', 'tahun', 'nama_kelas', 'nim'];

                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template.');
                }
            },
        ];
    }
}