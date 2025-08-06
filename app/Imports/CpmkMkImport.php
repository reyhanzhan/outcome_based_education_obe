<?php

namespace App\Imports;

use App\Models\Cpmk;
use App\Models\Mk;
use App\Models\CpmkMk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CpmkMkImport implements ToModel, WithStartRow, SkipsEmptyRows, WithEvents
{
    private $kodeProdi;
    private $mks;
    private $kurikulumId;

    public function __construct($kodeProdi, $kurikulumId)
    {
        $this->kodeProdi = $kodeProdi;
        $this->kurikulumId = $kurikulumId;
        $this->mks = Mk::where('kode_prodi', $kodeProdi)->pluck('id', 'kode_mk')->toArray();
        Log::info("CpmkMkImport initialized with kodeProdi: {$this->kodeProdi}, kurikulumId: {$this->kurikulumId}");
    }

    public function model(array $row)
    {
        // Log untuk debugging
        Log::debug('Processing row: ' . json_encode($row));

        // Cek apakah larik $row kosong atau tidak memiliki kunci indeks 1 (Kolom B)
        if (empty($row) || !isset($row[1]) || trim($row[1]) === '') {
            Log::warning('Skipping empty or invalid row during CPMK-MK import.');
            return null; // Abaikan baris yang benar-benar kosong atau tanpa Kode CPMK
        }

        // Indeks kolom: 0 = No, 1 = Kode CPMK, 2+ = MK
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
        foreach ($this->mks as $mkCode => $mkId) {
            // Pastikan indeks kolom MK ada di larik $row sebelum diakses
            if (isset($row[$colIndex])) {
                $cellValue = trim(strtolower($row[$colIndex]));

                // Cek apakah ada indikator pemetaan
                if (in_array($cellValue, ['v', '✔', '✓', 'x', 'yes'])) {
                    $hasMapping = true;
                    // Periksa apakah entri sudah ada
                    $existingMapping = CpmkMk::where('cpmk_id', $cpmk->id)
                                          ->where('mk_id', $mkId)
                                          ->where('kurikulum_id', $this->kurikulumId)
                                          ->first();

                    if ($existingMapping) {
                        // Perbarui entri yang ada
                        $existingMapping->update([
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $mappings[] = $existingMapping;
                        Log::info("Updated existing mapping: cpmk_id={$cpmk->id}, mk_id={$mkId}, kurikulum_id={$this->kurikulumId}, id={$existingMapping->id}");
                    } else {
                        // Buat entri baru
                        $mapping = new CpmkMk([
                            'cpmk_id' => $cpmk->id,
                            'mk_id' => $mkId,
                            'kurikulum_id' => $this->kurikulumId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $mapping->save();
                        $mappings[] = $mapping;
                        Log::info("Mapping saved: cpmk_id={$cpmk->id}, mk_id={$mkId}, kurikulum_id={$this->kurikulumId}, id=" . ($mapping->id ?? 'not saved'));
                    }
                } else {
                    // Hapus pemetaan yang ada di database
                    CpmkMk::where('cpmk_id', $cpmk->id)
                         ->where('mk_id', $mkId)
                         ->where('kurikulum_id', $this->kurikulumId)
                         ->delete();
                    Log::info("Deleted mapping: cpmk_id={$cpmk->id}, mk_id={$mkId}, kurikulum_id={$this->kurikulumId}");
                }
            } else {
                // Jika kolom tidak ada, hapus pemetaan yang ada
                CpmkMk::where('cpmk_id', $cpmk->id)
                     ->where('mk_id', $mkId)
                     ->where('kurikulum_id', $this->kurikulumId)
                     ->delete();
            }
            $colIndex++;
        }

        // Jika tidak ada pemetaan, hapus semua pemetaan untuk CPMK ini dan kembalikan null
        if (!$hasMapping) {
            CpmkMk::where('cpmk_id', $cpmk->id)
                 ->where('kurikulum_id', $this->kurikulumId)
                 ->delete();
            Log::info("Tidak ada pemetaan untuk CPMK '{$kodeCpmk}'. Menghapus semua pemetaan terkait.");
            return null; // Tidak ada model baru untuk disimpan
        }

        // Kembalikan semua pemetaan yang valid
        return $mappings;
    }

    public function startRow(): int
    {
        return 3; // Melewati baris header utama dan detail MK
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

                if ($highestRow < 3) {
                    throw new \Exception('File Excel kosong atau tidak memiliki cukup baris.');
                }

                // Ambil header dari baris pertama (A1:B1)
                $headerRow1 = $worksheet->rangeToArray('A1:B1', null, true, true, true)[1] ?? [];
                Log::debug('Header Row 1: ' . json_encode($headerRow1));

                // Validasi header A1 dan B1
                if (!isset($headerRow1['A']) || trim($headerRow1['A']) !== 'No' || !isset($headerRow1['B']) || trim($headerRow1['B']) !== 'Kode CPMK') {
                    throw new \Exception('File Excel tidak sesuai dengan template. Header baris pertama harus berisi "No" di kolom A dan "Kode CPMK" di kolom B.');
                }

                // Ambil header MK dari baris kedua
                $startColIndex = 3; // Mulai dari kolom C
                $endColIndex = 2 + count($this->mks); // 2 = A dan B, lalu tambah jumlah MK
                $endCol = Coordinate::stringFromColumnIndex($endColIndex);
                $mkHeaders = $worksheet->rangeToArray('C2:' . $endCol . '2', null, true, true, true)[2] ?? [];
                Log::debug('MK Headers: ' . json_encode($mkHeaders));

                $expectedMkCodes = array_keys($this->mks);
                $actualMkCodes = array_filter($mkHeaders, fn($value) => !is_null($value) && trim($value) !== '');

                // Validasi bahwa semua kode MK di header sesuai dengan database
                foreach ($actualMkCodes as $mkCode) {
                    if (!in_array(trim($mkCode), $expectedMkCodes)) {
                        throw new \Exception("Kode MK '$mkCode' di header tidak ditemukan di database.");
                    }
                }

                // Validasi jumlah header MK
                if (count($actualMkCodes) !== count($expectedMkCodes)) {
                    throw new \Exception('Jumlah header MK tidak sesuai dengan data di database. Diharapkan ' . count($expectedMkCodes) . ', ditemukan ' . count($actualMkCodes));
                }
            },
        ];
    }
}