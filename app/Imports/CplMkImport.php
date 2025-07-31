<?php

namespace App\Imports;

use App\Models\Cpl;
use App\Models\Mk;
use App\Models\CplMk;
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
    private $kurikulumId;

    public function __construct($kodeProdi, $kurikulumId)
    {
        $this->kodeProdi = $kodeProdi;
        $this->kurikulumId = $kurikulumId;
        $this->cpls = Cpl::where('kode_prodi', $kodeProdi)->pluck('id', 'kode_cpl')->toArray();
        Log::info("CplMkImport initialized with kurikulumId: {$this->kurikulumId}");
    }

    public function model(array $row)
    {
        Log::debug('Processing row: ' . json_encode($row));

        if (empty($row) || !isset($row[1]) || trim($row[1]) === '') {
            Log::warning('Skipping empty or invalid row during CPL-MK import.');
            return null;
        }

        $kodeMk = trim($row[1]);
        $mk = Mk::where('kode_prodi', $this->kodeProdi)->where('kode_mk', $kodeMk)->first();

        if (!$mk) {
            Log::warning("Kode MK '{$kodeMk}' tidak ditemukan di database. Abaikan baris.");
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
                    $mapping = new CplMk([
                        'cpl_id' => $cplId,
                        'mk_id' => $mk->id,
                        'kurikulum_id' => $this->kurikulumId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $mapping->save();
                    $mappings[] = $mapping;
                    Log::info("Mapping saved: cpl_id={$cplId}, mk_id={$mk->id}, kurikulum_id={$this->kurikulumId}, id=" . ($mapping->id ?? 'not saved'));
                } else {
                    DB::table('cpl_mk')
                        ->where('cpl_id', $cplId)
                        ->where('mk_id', $mk->id)
                        ->where('kurikulum_id', $this->kurikulumId)
                        ->delete();
                }
            } else {
                DB::table('cpl_mk')
                    ->where('cpl_id', $cplId)
                    ->where('mk_id', $mk->id)
                    ->where('kurikulum_id', $this->kurikulumId)
                    ->delete();
            }
            $colIndex++;
        }

        if (!$hasMapping) {
            DB::table('cpl_mk')
                ->where('mk_id', $mk->id)
                ->where('kurikulum_id', $this->kurikulumId)
                ->delete();
            Log::info("No mapping for MK '{$kodeMk}' with kurikulum_id {$this->kurikulumId}. All related mappings deleted.");
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

                if (!isset($headerRow1['A']) || trim($headerRow1['A']) !== 'No' || !isset($headerRow1['B']) || trim($headerRow1['B']) !== 'Kode MK') {
                    throw new \Exception('File Excel tidak sesuai dengan template. Header baris pertama harus berisi "No" di kolom A dan "Kode MK" di kolom B.');
                }

                $startColIndex = 3;
                $endColIndex = 2 + count($this->cpls);
                $endCol = Coordinate::stringFromColumnIndex($endColIndex);
                $cplHeaders = $worksheet->rangeToArray('C1:' . $endCol . '1', null, true, true, true)[1] ?? [];
                Log::debug('CPL Headers: ' . json_encode($cplHeaders));

                $expectedCplCodes = array_keys($this->cpls);
                $actualCplCodes = array_filter($cplHeaders, fn($value) => !is_null($value) && trim($value) !== '');

                foreach ($actualCplCodes as $cplCode) {
                    if (!in_array(trim($cplCode), $expectedCplCodes)) {
                        throw new \Exception("Kode CPL '$cplCode' di header tidak ditemukan di database.");
                    }
                }

                if (count($actualCplCodes) !== count($expectedCplCodes)) {
                    throw new \Exception('Jumlah header CPL tidak sesuai dengan data di database. Diharapkan ' . count($expectedCplCodes) . ', ditemukan ' . count($actualCplCodes));
                }
            },
        ];
    }
}