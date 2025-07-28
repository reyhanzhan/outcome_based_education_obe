<?php

namespace App\Imports;

use App\Models\Cpmk;
use App\Models\Cpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CpmkCplImport implements ToModel, WithStartRow, SkipsEmptyRows, WithEvents
{
    private $kodeProdi;
    private $cpls;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
        $this->cpls = Cpl::where('kode_prodi', $kodeProdi)->pluck('id', 'kode_cpl')->toArray();
    }

    public function model(array $row)
    {
        // Log untuk debugging
        Log::debug('Processing row: ' . json_encode($row));

        // Cek apakah larik $row kosong atau tidak memiliki kunci indeks 1 (Kolom B)
        if (empty($row) || !isset($row[1]) || trim($row[1]) === '') {
            Log::warning('Skipping empty or invalid row during CPMK-CPL import.');
            return null; // Abaikan baris yang benar-benar kosong atau tanpa Kode CPMK
        }

        // Indeks kolom: 0 = No, 1 = Kode CPMK, 2+ = CPL
        $kodeCpmk = trim($row[1]);

        // Temukan CPMK berdasarkan kode prodi dan kode CPMK
        $cpmk = Cpmk::where('kode_prodi', $this->kodeProdi)->where('kode_cpmk', $kodeCpmk)->first();

        // Jika CPMK tidak ditemukan, abaikan baris ini
        if (!$cpmk) {
            Log::warning("Kode CPMK '{$kodeCpmk}' tidak ditemukan di database. Abaikan baris.");
            return null;
        }

        $mappings = []; // Simpan pemetaan yang akan disimpan
        $hasMapping = false; // Flag untuk mengecek apakah ada pemetaan

        $colIndex = 2; // Mulai dari kolom C
        foreach ($this->cpls as $cplCode => $cplId) {
            // Pastikan indeks kolom CPL ada di larik $row sebelum diakses
            if (isset($row[$colIndex])) {
                $cellValue = trim(strtolower($row[$colIndex]));

                // Cek apakah ada indikator pemetaan
                if (in_array($cellValue, ['v', '✔', '✓', 'x', 'yes'])) {
                    $hasMapping = true;
                    $mappings[] = new \App\Models\CpmkCpl([
                        'cpmk_id' => $cpmk->id,
                        'cpl_id' => $cplId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Hapus pemetaan yang ada di database
                    DB::table('cpmk_cpl')
                        ->where('cpmk_id', $cpmk->id)
                        ->where('cpl_id', $cplId)
                        ->delete();
                }
            } else {
                // Jika kolom tidak ada, hapus pemetaan yang ada
                DB::table('cpmk_cpl')
                    ->where('cpmk_id', $cpmk->id)
                    ->where('cpl_id', $cplId)
                    ->delete();
            }
            $colIndex++;
        }

        // Jika tidak ada pemetaan, hapus semua pemetaan untuk CPMK ini dan kembalikan null
        if (!$hasMapping) {
            DB::table('cpmk_cpl')
                ->where('cpmk_id', $cpmk->id)
                ->delete();
            Log::info("Tidak ada pemetaan untuk CPMK '{$kodeCpmk}'. Menghapus semua pemetaan terkait.");
            return null; // Tidak ada model baru untuk disimpan
        }

        // Kembalikan semua pemetaan yang valid
        return $mappings;
    }

    public function startRow(): int
    {
        return 3; // Melewati baris header utama dan detail CPL
    }

    public function onFailure(Failure ...$failures)
    {
        $failure = $failures[0];
        $message = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        Log::error($message); // Tambahkan log untuk debugging
        throw new \Exception($message);
    }

   public function registerEvents(): array
{
    return [
        BeforeImport::class => function (BeforeImport $event) {
            $worksheet = $event->reader->getDelegate()->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            if ($highestRow < 2) {
                throw new \Exception('File Excel kosong atau tidak memiliki cukup baris.');
            }

            // Ambil header dari baris pertama (A1:B1)
            $headerRow1 = $worksheet->rangeToArray('A1:B1', null, true, true, true)[1] ?? [];
            Log::debug('Header Row 1: ' . json_encode($headerRow1));

            // Validasi header A1 dan B1
            if (!isset($headerRow1['A']) || trim($headerRow1['A']) !== 'No' ||
                !isset($headerRow1['B']) || trim($headerRow1['B']) !== 'Kode CPMK') {
                throw new \Exception('File Excel tidak sesuai dengan template. Header baris pertama harus berisi "No" di kolom A dan "Kode CPMK" di kolom B.');
            }

            // Ambil header CPL dari baris pertama (mulai dari C1)
            $startColIndex = 3; // Mulai dari kolom C
            $endColIndex = 2 + count($this->cpls); // 2 = A dan B, lalu tambah jumlah CPL
            $endCol = Coordinate::stringFromColumnIndex($endColIndex);
            Log::debug("Validating CPL headers from C1 to {$endCol}1, expected count: " . count($this->cpls));
            $cplHeaders = $worksheet->rangeToArray('C1:' . $endCol . '1', null, true, true, true)[1] ?? [];
            Log::debug('Raw CPL Headers: ' . json_encode($cplHeaders));

            $expectedCplCodes = array_keys($this->cpls);
            $actualCplCodes = array_filter($cplHeaders, fn($value) => !is_null($value) && trim($value) !== '');
            Log::debug('Filtered CPL Headers: ' . json_encode($actualCplCodes));

            // Validasi bahwa semua kode CPL di header sesuai dengan database
            foreach ($actualCplCodes as $cplCode) {
                if (!in_array(trim($cplCode), $expectedCplCodes)) {
                    throw new \Exception("Kode CPL '$cplCode' di header tidak ditemukan di database untuk kode_prodi {$this->kodeProdi}.");
                }
            }

            // Validasi jumlah header CPL
            if (count($actualCplCodes) !== count($expectedCplCodes)) {
                throw new \Exception('Jumlah header CPL tidak sesuai dengan data di database. Diharapkan ' . count($expectedCplCodes) . ', ditemukan ' . count($actualCplCodes) . '. Raw headers: ' . json_encode($cplHeaders));
            }
        },
    ];
}
}