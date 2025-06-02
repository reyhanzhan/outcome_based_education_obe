<?php

namespace App\Imports;

use App\Models\Mk;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;

class MkImport implements ToModel, WithHeadingRow, WithValidation, WithStartRow, WithEvents
{
    use Importable;

    private $kodeProdi;
    private $updatedRecords = 0;
    private $skippedRecords = 0;

    public function __construct($kodeProdi)
    {
        $this->kodeProdi = $kodeProdi;
    }

    public function model(array $row)
    {
        // Konversi semua nilai ke tipe data yang sesuai
        $kodeMk = (string)$row['kode_mk'];
        $deskripsi = (string)$row['deskripsi'];
        $sks = (int)$row['sks'];
        $jenisMk = (string)$row['jenis_mata_kuliah'];

        Log::info("Processing row - kode_mk: {$kodeMk}, deskripsi: {$deskripsi}, sks: {$sks}, jenis_mk: {$jenisMk}");

        $existingRecord = Mk::where('kode_mk', $kodeMk)->where('kode_prodi', $this->kodeProdi)->first();

        if ($existingRecord) {
            // Jika data sudah ada, perbarui
            $existingRecord->update([
                'deskripsi' => $deskripsi,
                'sks' => $sks,
                'jenis_mk' => $jenisMk,
            ]);
            $this->updatedRecords++;
            Log::info("Updated existing MK: {$kodeMk} for kode_prodi: {$this->kodeProdi}");
        } else {
            // Jika data baru, buat
            return new Mk([
                'kode_mk' => $kodeMk,
                'deskripsi' => $deskripsi,
                'sks' => $sks,
                'jenis_mk' => $jenisMk,
                'kode_prodi' => $this->kodeProdi,
            ]);
        }

        return null; // Kembalikan null untuk baris yang diperbarui
    }

    public function rules(): array
    {
        return [
            'kode_mk' => 'required|max:255',
            'deskripsi' => 'required|string',
            'sks' => 'required|integer|min:1',
            'jenis_mata_kuliah' => 'required|string', // Hapus in:Kuliah,Skripsi
        ];
    }

    public function customValidationMessages()
    {
        return [
            'kode_mk.required' => 'Baris :row: Kode MK wajib diisi.',
            'kode_mk.max' => 'Baris :row: Kode MK terlalu panjang (maksimal 255 karakter).',
            'deskripsi.required' => 'Baris :row: Deskripsi wajib diisi.',
            'sks.required' => 'Baris :row: SKS wajib diisi.',
            'sks.integer' => 'Baris :row: SKS harus berupa angka bulat.',
            'sks.min' => 'Baris :row: SKS minimal 1.',
            'jenis_mata_kuliah.required' => 'Baris :row: Jenis Mata Kuliah wajib diisi.',
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
        Log::warning($message);
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
                $expectedHeadings = ['kode_mk', 'deskripsi', 'sks', 'jenis_mata_kuliah'];

                if (array_diff($expectedHeadings, $headings) || array_diff($headings, $expectedHeadings)) {
                    throw new \Exception('File Excel tidak sesuai dengan template.');
                }
            },
            AfterImport::class => function (AfterImport $event) {
                if ($this->updatedRecords > 0) {
                    Log::info("Total records updated: {$this->updatedRecords}");
                }
                if ($this->skippedRecords > 0) {
                    Log::warning("Total records skipped due to duplicates: {$this->skippedRecords}");
                }
            },
        ];
    }
}