<?php

namespace App\Imports;

use App\Models\Cpl;
use App\Models\Pl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class CplPlImport implements ToModel, WithStartRow, SkipsEmptyRows, WithEvents
{
    private $kodeProdi;
    private $pls;
    private $kurikulumId;

    public function __construct($kodeProdi, $kurikulumId)
    {
        $this->kodeProdi = $kodeProdi;
        $this->kurikulumId = $kurikulumId; // Pastikan ini diterima
        Log::info("CplPlImport initialized with kurikulumId: {$this->kurikulumId}");
        $this->pls = Pl::where('kode_prodi', $kodeProdi)->pluck('id', 'kode_pl')->toArray();
    }

    public function model(array $row)
    {
        if (empty($row) || !isset($row[1]) || trim($row[1]) === '') {
            Log::warning('Skipping empty or invalid row during CPL-PL import.');
            return null;
        }

        $kodeCpl = trim($row[1]);
        $cpl = Cpl::where('kode_prodi', $this->kodeProdi)->where('kode_cpl', $kodeCpl)->first();

        if (!$cpl) {
            Log::warning("Kode CPL '{$kodeCpl}' tidak ditemukan di database. Abaikan baris.");
            return null;
        }

        $mappings = [];
        $hasMapping = false;

        $colIndex = 2;
        foreach ($this->pls as $plCode => $plId) {
            if (isset($row[$colIndex])) {
                $cellValue = trim(strtolower($row[$colIndex]));
                if (in_array($cellValue, ['v', '✔', '✓', 'x', 'yes'])) {
                    $hasMapping = true;
                    $mappings[] = new \App\Models\CplPl([
                        'cpl_id' => $cpl->id,
                        'pl_id' => $plId,
                        'kurikulum_id' => $this->kurikulumId, // Pastikan ini selalu digunakan
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    Log::info("Mapping created: cpl_id={$cpl->id}, pl_id={$plId}, kurikulum_id={$this->kurikulumId}");
                } else {
                    DB::table('cpl_pl')
                        ->where('cpl_id', $cpl->id)
                        ->where('pl_id', $plId)
                        ->where('kurikulum_id', $this->kurikulumId)
                        ->delete();
                    Log::info("Mapping deleted: cpl_id={$cpl->id}, pl_id={$plId}, kurikulum_id={$this->kurikulumId}");
                }
            } else {
                DB::table('cpl_pl')
                    ->where('cpl_id', $cpl->id)
                    ->where('pl_id', $plId)
                    ->where('kurikulum_id', $this->kurikulumId)
                    ->delete();
                Log::info("Mapping deleted (missing cell): cpl_id={$cpl->id}, pl_id={$plId}, kurikulum_id={$this->kurikulumId}");
            }
            $colIndex++;
        }

        if (!$hasMapping) {
            DB::table('cpl_pl')
                ->where('cpl_id', $cpl->id)
                ->where('kurikulum_id', $this->kurikulumId)
                ->delete();
            Log::info("No mapping for CPL '{$kodeCpl}' with kurikulum_id {$this->kurikulumId}. All related mappings deleted.");
            return null;
        }

        return $mappings;
    }

    public function startRow(): int
    {
        return 3;
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

                if ($highestRow < 3) {
                    throw new \Exception('File Excel kosong atau tidak memiliki cukup baris.');
                }

                $headerRow1 = $worksheet->rangeToArray('A1:B1', null, true, true, true)[1] ?? [];
                Log::debug('Header Row 1: ' . json_encode($headerRow1));

                if (!isset($headerRow1['A']) || trim($headerRow1['A']) !== 'No' || !isset($headerRow1['B']) || trim($headerRow1['B']) !== 'Kode CPL') {
                    throw new \Exception('File Excel tidak sesuai dengan template.');
                }

                $plHeaders = $worksheet->rangeToArray('C2:' . chr(67 + count($this->pls)) . '2', null, true, true, true)[2] ?? [];
                Log::debug('PL Headers: ' . json_encode($plHeaders));

                $expectedPlCodes = array_keys($this->pls);
                $actualPlCodes = array_filter($plHeaders, fn($value) => !is_null($value) && trim($value) !== '');

                foreach ($actualPlCodes as $plCode) {
                    if (!in_array(trim($plCode), $expectedPlCodes)) {
                        throw new \Exception("Kode PL '$plCode' tidak ditemukan di database.");
                    }
                }

                if (count($actualPlCodes) !== count($expectedPlCodes)) {
                    throw new \Exception('Jumlah header PL tidak sesuai dengan data di database.');
                }
            },
        ];
    }
}