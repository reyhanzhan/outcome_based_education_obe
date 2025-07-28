<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CpmkCplImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class Cpmk_CplController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'kps') {
                abort(403, 'Akses hanya untuk KPS.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $cpmks = Cpmk::where('kode_prodi', $kodeProdi)->get();
            $cpls = Cpl::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpmk_cpl')
                ->whereIn('cpmk_id', $cpmks->pluck('id'))
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpmk_id . '-' . $item->cpl_id => true];
                });

            Log::info("Successfully loaded pemetaan CPMK-CPL for kode_prodi: {$kodeProdi}, CPMK count: {$cpmks->count()}, CPL count: {$cpls->count()}");

            return view('pemetaan_CPMK-CPL.index', compact('cpmks', 'cpls', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPMK-CPL: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pemetaan: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return response()->json(['error' => 'Kode prodi tidak ditemukan.'], 403);
            }

            Log::info('Received data: ' . json_encode($request->all()));

            $request->validate([
                'cpmk_id' => 'required|exists:cpmk,id',
                'cpl_id' => 'required|exists:cpl,id',
                'checked' => 'required|boolean',
            ]);

            $cpmk = Cpmk::where('id', $request->cpmk_id)->where('kode_prodi', $kodeProdi)->first();
            $cpl = Cpl::where('id', $request->cpl_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$cpmk || !$cpl) {
                return response()->json(['error' => 'CPMK atau CPL tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpmk_cpl')->updateOrInsert(
                    [
                        'cpmk_id' => $request->cpmk_id,
                        'cpl_id' => $request->cpl_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                Log::info("Pemetaan CPMK-CPL tersimpan: CPMK ID {$request->cpmk_id} - CPL ID {$request->cpl_id}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpmk_cpl')
                    ->where('cpmk_id', $request->cpmk_id)
                    ->where('cpl_id', $request->cpl_id)
                    ->delete();
                Log::info("Pemetaan CPMK-CPL dihapus: CPMK ID {$request->cpmk_id} - CPL ID {$request->cpl_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPMK-CPL: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
{
    try {
        $kodeProdi = Auth::user()->kode_prodi;
        if (!$kodeProdi) {
            throw new \Exception('Kode prodi tidak ditemukan untuk user ini.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ambil data CPL untuk header kolom
        $cpls = Cpl::where('kode_prodi', $kodeProdi)->get(['kode_cpl']);
        if ($cpls->isEmpty()) {
            throw new \Exception('Tidak ada data CPL untuk prodi ini.');
        }
        $cplCount = $cpls->count();

        // Ambil data CPMK untuk baris
        $cpmks = Cpmk::where('kode_prodi', $kodeProdi)->get(['kode_cpmk']);
        if ($cpmks->isEmpty()) {
            throw new \Exception('Tidak ada data CPMK untuk prodi ini.');
        }
        $cpmkCount = $cpmks->count();

        // Set header utama
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode CPMK');

        // Set header CPL di baris pertama (mulai dari kolom C) menggunakan kode_cpl
        $startColIndex = 3; // Mulai dari kolom C (index 3)
        foreach ($cpls as $cpl) {
            $colLetter = Coordinate::stringFromColumnIndex($startColIndex);
            $sheet->setCellValue($colLetter . '1', $cpl->kode_cpl); // Gunakan kode_cpl
            $startColIndex++;
        }
        $endCol = Coordinate::stringFromColumnIndex(2 + $cplCount); // A=1, B=2, lalu tambah CPL

        // Set header style
        $sheet->getStyle('A1:' . $endCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $endCol . '1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Debug: Tampilkan data yang diambil
        Log::info("CPL data retrieved for kode_prodi {$kodeProdi}: " . $cpls->pluck('kode_cpl')->toJson());
        Log::info("CPMK data retrieved for kode_prodi {$kodeProdi}: " . $cpmks->pluck('kode_cpmk')->toJson());

        // Ambil pemetaan dari database
        $pemetaan = DB::table('cpmk_cpl')
            ->whereIn('cpmk_id', $cpmks->pluck('id'))
            ->whereIn('cpl_id', $cpls->pluck('id'))
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->cpmk_id . '-' . $item->cpl_id => true];
            });

        // Isi data berdasarkan CPMK sebagai baris dan CPL sebagai kolom
        $row = 2;
        foreach ($cpmks as $index => $cpmk) {
            $sheet->setCellValue('A' . $row, $index + 1); // No
            $sheet->setCellValue('B' . $row, $cpmk->kode_cpmk ?? 'CPMK' . $cpmk->id); // Kode CPMK
            $colIndex = 3; // Mulai dari kolom C
            foreach ($cpls as $cpl) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($colLetter . $row, isset($pemetaan[$cpmk->id . '-' . $cpl->id]) ? 'V' : '');
                $colIndex++;
            }
            $row++;
        }

        // Auto-size columns
        $maxColIndex = 2 + $cplCount; // A=1, B=2, lalu tambah CPL
        for ($i = 1; $i <= $maxColIndex; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'cpmk_cpl_template');
        $writer->save($tempFile);

        Log::info("Template CPMK-CPL downloaded for kode_prodi: {$kodeProdi} with {$cplCount} CPL columns and {$cpmkCount} CPMK rows");
        return response()->download($tempFile, 'template_cpmk_cpl.xlsx')->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        Log::error('Error generating template: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh template: ' . $e->getMessage());
    }

    }

    public function import(Request $request)
{
    // Validasi file yang diunggah
    $request->validate([
        'file' => 'required|file|mimes:xls,xlsx,csv|max:2048', // Maksimum 2MB
    ]);

    try {
        // Ambil kode prodi dari user yang sedang login
        $kodeProdi = Auth::user()->kode_prodi;
        if (!$kodeProdi) {
            throw new \Exception('Kode prodi tidak ditemukan untuk user ini.');
        }

        // Proses impor file menggunakan CpmkCplImport
        Excel::import(new CpmkCplImport($kodeProdi), $request->file('file'));

        // Berikan pesan sukses
        return redirect()->back()->with('success', 'Data pemetaan CPMK-CPL berhasil diimpor.');
    } catch (\Exception $e) {
        // Log error untuk debugging
        Log::error('Import failed: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
    }
}
}