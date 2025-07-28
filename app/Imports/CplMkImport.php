<?php

namespace App\Imports;

use App\Models\Cpl;
use App\Models\Mk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CplMkImport implements ToModel, WithStartRow, SkipsEmptyRows, WithEvents
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
            Log::warning('Skipping empty or invalid row during CPL-MK import.');
            return null; // Abaikan baris yang benar-benar kosong atau tanpa Kode MK
        }

        // Indeks kolom: 0 = No, 1 = Kode MK, 2+ = CPL
        $kodeMk = trim($row[1]);

        // Temukan MK berdasarkan kode prodi dan kode MK
        $mk = Mk::where('kode_prodi', $this->kodeProdi)->where('kode_mk', $kodeMk)->first();

        // Jika MK tidak ditemukan, abaikan baris ini
        if (!$mk) {
            Log::warning("Kode MK '{$kodeMk}' tidak ditemukan di database. Abaikan baris.");
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
                    $mappings[] = new \App\Models\CplMk([
                        'cpl_id' => $cplId,
                        'mk_id' => $mk->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Hapus pemetaan yang ada di database
                    DB::table('cpl_mk')
                        ->where('cpl_id', $cplId)
                        ->where('mk_id', $mk->id)
                        ->delete();
                }
            } else {
                // Jika kolom tidak ada, hapus pemetaan yang ada
                DB::table('cpl_mk')
                    ->where('cpl_id', $cplId)
                    ->where('mk_id', $mk->id)
                    ->delete();
            }
            $colIndex++;
        }

        // Jika tidak ada pemetaan, hapus semua pemetaan untuk MK ini dan kembalikan null
        if (!$hasMapping) {
            DB::table('cpl_mk')
                ->where('mk_id', $mk->id)
                ->delete();
            Log::info("Tidak ada pemetaan untuk MK '{$kodeMk}'. Menghapus semua pemetaan terkait.");
            return null; // Tidak ada model baru untuk disimpan
        }

        // Kembalikan semua pemetaan yang valid
        return $mappings;
    }

    public function startRow(): int
    {
        return 2; // Mulai dari baris 2 karena header hanya di baris 1
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
                if (!isset($headerRow1['A']) || trim($headerRow1['A']) !== 'No' || !isset($headerRow1['B']) || trim($headerRow1['B']) !== 'Kode MK') {
                    throw new \Exception('File Excel tidak sesuai dengan template. Header baris pertama harus berisi "No" di kolom A dan "Kode MK" di kolom B.');
                }

                // Ambil header CPL dari baris pertama (mulai dari C1)
                $startColIndex = 3; // Mulai dari kolom C
                $endColIndex = 2 + count($this->cpls); // 2 = A dan B, lalu tambah jumlah CPL
                $endCol = Coordinate::stringFromColumnIndex($endColIndex);
                $cplHeaders = $worksheet->rangeToArray('C1:' . $endCol . '1', null, true, true, true)[1] ?? [];
                Log::debug('CPL Headers: ' . json_encode($cplHeaders));

                $expectedCplCodes = array_keys($this->cpls);
                $actualCplCodes = array_filter($cplHeaders, fn($value) => !is_null($value) && trim($value) !== '');

                // Validasi bahwa semua kode CPL di header sesuai dengan database
                foreach ($actualCplCodes as $cplCode) {
                    if (!in_array(trim($cplCode), $expectedCplCodes)) {
                        throw new \Exception("Kode CPL '$cplCode' di header tidak ditemukan di database.");
                    }
                }

                // Validasi jumlah header CPL
                if (count($actualCplCodes) !== count($expectedCplCodes)) {
                    throw new \Exception('Jumlah header CPL tidak sesuai dengan data di database. Diharapkan ' . count($expectedCplCodes) . ', ditemukan ' . count($actualCplCodes));
                }
            },
        ];
    }
}