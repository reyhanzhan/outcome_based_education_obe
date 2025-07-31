<?php

namespace App\Imports;

use App\Models\Cpmk;
use App\Models\Cpl;
use App\Models\CpmkCpl;
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
    private $kurikulumId;

    public function __construct($kodeProdi, $kurikulumId)
    {
        $this->kodeProdi = $kodeProdi;
        $this->kurikulumId = $kurikulumId;
        $this->cpls = Cpl::where('kode_prodi', $kodeProdi)->pluck('id', 'kode_cpl')->toArray();
        Log::info("CpmkCplImport initialized with kurikulumId: {$this->kurikulumId}");
    }

    public function model(array $row)
{
    Log::debug('Processing row: ' . json_encode($row));

    if (empty($row) || !isset($row[1]) || trim($row[1]) === '') {
        Log::warning('Skipping empty or invalid row during CPMK-CPL import.');
        return null;
    }

    $kodeCpmk = trim($row[1]);
    $cpmk = Cpmk::where('kode_prodi', $this->kodeProdi)->where('kode_cpmk', $kodeCpmk)->first();

    if (!$cpmk) {
        Log::warning("Kode CPMK '{$kodeCpmk}' tidak ditemukan di database. Abaikan baris.");
        return null;
    }

    $mappings = [];
    $hasMapping = false;

    $colIndex = 2;
    foreach ($this->cpls as $cplCode => $cplId) {
        if (isset($row[$colIndex])) {
            $cellValue = trim(strtolower($row[$colIndex]));
            if (in_array($cellValue, ['v', '✔', '✓', 'x', 'yes'])) {
                $hasMapping = true;
                $mapping = new CpmkCpl([
                    'cpmk_id' => $cpmk->id,
                    'cpl_id' => $cplId,
                    'kurikulum_id' => $this->kurikulumId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $mapping->save();
                $mappings[] = $mapping;
                Log::info("Mapping saved: cpmk_id={$cpmk->id}, cpl_id={$cplId}, kurikulum_id={$this->kurikulumId}, id=" . ($mapping->id ?? 'not saved'));
            } else {
                // Hapus pemetaan untuk kombinasi ini jika ada
                DB::table('cpmk_cpl')
                    ->where('cpmk_id', $cpmk->id)
                    ->where('cpl_id', $cplId)
                    ->where('kurikulum_id', '!=', $this->kurikulumId) // Hapus data dari kurikulum lain
                    ->orWhere(function ($query) use ($cpmk, $cplId) {
                        $query->where('cpmk_id', $cpmk->id)
                              ->where('cpl_id', $cplId)
                              ->whereNull('kurikulum_id'); // Hapus data tanpa kurikulum_id
                    })
                    ->delete();
            }
        }
        $colIndex++;
    }

    if (!$hasMapping) {
        DB::table('cpmk_cpl')
            ->where('cpmk_id', $cpmk->id)
            ->where('kurikulum_id', $this->kurikulumId)
            ->delete();
        Log::info("No mapping for CPMK '{$kodeCpmk}' with kurikulum_id {$this->kurikulumId}. All related mappings deleted.");
        return null;
    }

    return $mappings;
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
                    throw new \Exception('File Excel kosong atau tidak memiliki cukup baris.');
                }

                $headerRow1 = $worksheet->rangeToArray('A1:B1', null, true, true, true)[1] ?? [];
                Log::debug('Header Row 1: ' . json_encode($headerRow1));

                if (!isset($headerRow1['A']) || trim($headerRow1['A']) !== 'No' ||
                    !isset($headerRow1['B']) || trim($headerRow1['B']) !== 'Kode CPMK') {
                    throw new \Exception('File Excel tidak sesuai dengan template. Header baris pertama harus berisi "No" di kolom A dan "Kode CPMK" di kolom B.');
                }

                $startColIndex = 3;
                $endColIndex = 2 + count($this->cpls);
                $endCol = Coordinate::stringFromColumnIndex($endColIndex);
                Log::debug("Validating CPL headers from C1 to {$endCol}1, expected count: " . count($this->cpls));
                $cplHeaders = $worksheet->rangeToArray('C1:' . $endCol . '1', null, true, true, true)[1] ?? [];
                Log::debug('Raw CPL Headers: ' . json_encode($cplHeaders));

                $expectedCplCodes = array_keys($this->cpls);
                $actualCplCodes = array_filter($cplHeaders, fn($value) => !is_null($value) && trim($value) !== '');
                Log::debug('Filtered CPL Headers: ' . json_encode($actualCplCodes));

                foreach ($actualCplCodes as $cplCode) {
                    if (!in_array(trim($cplCode), $expectedCplCodes)) {
                        throw new \Exception("Kode CPL '$cplCode' di header tidak ditemukan di database untuk kode_prodi {$this->kodeProdi}.");
                    }
                }

                if (count($actualCplCodes) !== count($expectedCplCodes)) {
                    throw new \Exception('Jumlah header CPL tidak sesuai dengan data di database. Diharapkan ' . count($expectedCplCodes) . ', ditemukan ' . count($actualCplCodes) . '. Raw headers: ' . json_encode($cplHeaders));
                }
            },
        ];
    }
}