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

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
        $this->pls = Pl::where('kode_prodi', $kodeProdi)->pluck('id', 'kode_pl')->toArray();
    }

    public function model(array $row)
{
    // Cek apakah larik $row kosong atau tidak memiliki kunci indeks 1 (Kolom B)
    if (empty($row) || !isset($row[1]) || trim($row[1]) === '') {
        Log::warning('Skipping empty or invalid row during CPL-PL import.');
        return null; // Abaikan baris yang benar-benar kosong atau tanpa Kode CPL
    }

    // Indeks kolom: 0 = No, 1 = Kode CPL, 2+ = PL
    $kodeCpl = trim($row[1]);

    // Temukan CPL berdasarkan kode prodi dan kode CPL
    $cpl = Cpl::where('kode_prodi', $this->kodeProdi)->where('kode_cpl', $kodeCpl)->first();

    // Jika CPL tidak ditemukan, abaikan baris ini
    if (!$cpl) {
        Log::warning("Kode CPL '{$kodeCpl}' tidak ditemukan di database. Abaikan baris.");
        return null;
    }

    $mappings = []; // Simpan pemetaan yang akan disimpan
    $hasMapping = false; // Flag untuk mengecek apakah ada pemetaan

    $colIndex = 2; // Mulai dari kolom C
    foreach ($this->pls as $plCode => $plId) {
        // Pastikan indeks kolom PL ada di larik $row sebelum diakses
        if (isset($row[$colIndex])) {
            $cellValue = trim(strtolower($row[$colIndex]));

            // Cek apakah ada indikator pemetaan
            if (in_array($cellValue, ['v', '✔', '✓', 'x', 'yes'])) {
                $hasMapping = true;
                $mappings[] = new \App\Models\CplPl([
                    'cpl_id' => $cpl->id,
                    'pl_id' => $plId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Hapus pemetaan yang ada di database
                DB::table('cpl_pl')
                    ->where('cpl_id', $cpl->id)
                    ->where('pl_id', $plId)
                    ->delete();
            }
        } else {
            // Jika kolom tidak ada, hapus pemetaan yang ada
            DB::table('cpl_pl')
                ->where('cpl_id', $cpl->id)
                ->where('pl_id', $plId)
                ->delete();
        }
        $colIndex++;
    }

    // Jika tidak ada pemetaan, hapus semua pemetaan untuk CPL ini dan kembalikan null
    if (!$hasMapping) {
        DB::table('cpl_pl')
            ->where('cpl_id', $cpl->id)
            ->delete();
        Log::info("Tidak ada pemetaan untuk CPL '{$kodeCpl}'. Menghapus semua pemetaan terkait.");
        return null; // Tidak ada model baru untuk disimpan
    }

    // Kembalikan semua pemetaan yang valid
    return $mappings;
}

    public function startRow(): int
    {
        return 3; // Melewati baris header utama dan detail PL
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
            if (!isset($headerRow1['A']) || trim($headerRow1['A']) !== 'No' || !isset($headerRow1['B']) || trim($headerRow1['B']) !== 'Kode CPL') {
                throw new \Exception('File Excel tidak sesuai dengan template. Header baris pertama harus berisi "No" di kolom A dan "Kode CPL" di kolom B.');
            }

            // Ambil header PL dari baris kedua
            $plHeaders = $worksheet->rangeToArray('C2:' . chr(67 + count($this->pls)) . '2', null, true, true, true)[2] ?? [];
            Log::debug('PL Headers: ' . json_encode($plHeaders));

            $expectedPlCodes = array_keys($this->pls);
            $actualPlCodes = array_filter($plHeaders, fn($value) => !is_null($value) && trim($value) !== '');

            // Validasi bahwa semua kode PL di header sesuai dengan database
            foreach ($actualPlCodes as $plCode) {
                if (!in_array(trim($plCode), $expectedPlCodes)) {
                    throw new \Exception("Kode PL '$plCode' di header tidak ditemukan di database.");
                }
            }

            // Validasi jumlah header PL
            if (count($actualPlCodes) !== count($expectedPlCodes)) {
                throw new \Exception('Jumlah header PL tidak sesuai dengan data di database. Diharapkan ' . count($expectedPlCodes) . ', ditemukan ' . count($actualPlCodes));
            }
        },
    ];
}
}