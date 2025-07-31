<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Kurikulum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Support\Facades\Log;

class KelasImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;
    private $tahunFilter;

    public function __construct($kodeProdi, $tahunFilter)
    {
        $this->kodeProdi = $kodeProdi;
        $this->tahunFilter = $tahunFilter;
        Log::info("KelasImport initialized with kodeProdi: {$this->kodeProdi}, tahunFilter: {$this->tahunFilter}");
    }

    public function model(array $row)
    {
        Log::debug('Processing row: ' . json_encode($row));

        $tahun = trim($row['tahun'] ?? '');
        if (empty($tahun)) {
            Log::warning("Skipping row due to empty tahun, row: " . json_encode($row));
            return null;
        }

        if ($tahun !== $this->tahunFilter) {
            Log::warning("Skipping row with tahun {$tahun}, expected {$this->tahunFilter}, row: " . json_encode($row));
            return null;
        }

        // Cari kurikulum_id berdasarkan tahun dan kode_prodi
        $kurikulumId = Kurikulum::where('kode_prodi', $this->kodeProdi)
                              ->where('tahun', $this->tahunFilter)
                              ->value('id');

        if (!$kurikulumId) {
            Log::warning("No kurikulum found for kodeProdi: {$this->kodeProdi}, tahunFilter: {$this->tahunFilter}");
            $kurikulumId = null; // Atau buat kurikulum baru jika diperlukan
        }

        return new Kelas([
            'tahun' => $tahun,
            'kode_mk' => trim($row['kode_mk'] ?? ''),
            'periode' => trim($row['periode'] ?? ''),
            'nip_dosen' => trim($row['nip_dosen'] ?? ''),
            'kode_prodi' => $this->kodeProdi,
            'kurikulum_id' => $kurikulumId,
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
            'tahun.required' => 'Baris :row: Kolom tahun wajib diisi.',
            'tahun.numeric' => 'Baris :row: Tahun harus berupa angka.',
            'tahun.min' => 'Baris :row: Tahun harus minimal 2000.',
            'tahun.max' => 'Baris :row: Tahun tidak boleh lebih dari 2025.',
            'kode_mk.required' => 'Baris :row: Kolom kode mata kuliah wajib diisi.',
            'kode_mk.exists' => 'Baris :row: Kode mata kuliah :input tidak valid.',
            'nip_dosen.required' => 'Baris :row: Kolom NIP dosen wajib diisi.',
            'nip_dosen.exists' => 'Baris :row: NIP dosen :input tidak valid.',
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
        Log::error($message);
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