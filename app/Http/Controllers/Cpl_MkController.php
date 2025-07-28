<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CplMkImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;



class Cpl_MkController extends Controller
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

            $cpls = Cpl::where('kode_prodi', $kodeProdi)->get();
            $mks = Mk::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpl_mk')
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->whereIn('mk_id', $mks->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpl_id . '-' . $item->mk_id => true];
                });

            Log::info("Successfully loaded pemetaan CPL-MK for kode_prodi: {$kodeProdi}, CPL count: {$cpls->count()}, MK count: {$mks->count()}");

            return view('pemetaan_CPL-MK.index', compact('cpls', 'mks', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPL-MK: ' . $e->getMessage());
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
                'cpl_id' => 'required|exists:cpl,id',
                'mk_id' => 'required|exists:mk,id',
                'checked' => 'required|boolean',
            ]);

            $cpl = Cpl::where('id', $request->cpl_id)->where('kode_prodi', $kodeProdi)->first();
            $mk = Mk::where('id', $request->mk_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$cpl || !$mk) {
                return response()->json(['error' => 'CPL atau MK tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpl_mk')->updateOrInsert(
                    [
                        'cpl_id' => $request->cpl_id,
                        'mk_id' => $request->mk_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                Log::info("Pemetaan CPL-MK tersimpan: CPL ID {$request->cpl_id} - MK ID {$request->mk_id}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpl_mk')
                    ->where('cpl_id', $request->cpl_id)
                    ->where('mk_id', $request->mk_id)
                    ->delete();
                Log::info("Pemetaan CPL-MK dihapus: CPL ID {$request->cpl_id} - MK ID {$request->mk_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPL-MK: ' . $e->getMessage());
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

            // Ambil data MK untuk baris
            $mks = Mk::where('kode_prodi', $kodeProdi)->get(['kode_mk']);
            if ($mks->isEmpty()) {
                throw new \Exception('Tidak ada data MK untuk prodi ini.');
            }
            $mkCount = $mks->count();

            // Ambil data CPL untuk header kolom
            $cpls = Cpl::where('kode_prodi', $kodeProdi)->get(['kode_cpl']);
            if ($cpls->isEmpty()) {
                throw new \Exception('Tidak ada data CPL untuk prodi ini.');
            }
            $cplCount = $cpls->count();

            // Set header utama
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Kode MK');

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
            $sheet->getStyle('A1:' . $endCol . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Debug: Tampilkan data yang diambil
            Log::info("MK data retrieved for kode_prodi {$kodeProdi}: " . $mks->pluck('kode_mk')->toJson());
            Log::info("CPL data retrieved for kode_prodi {$kodeProdi}: " . $cpls->pluck('kode_cpl')->toJson());

            // Ambil pemetaan dari database
            $pemetaan = DB::table('cpl_mk')
                ->whereIn('mk_id', $mks->pluck('id'))
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->mk_id . '-' . $item->cpl_id => true]; // Perbaiki pemetaan
                });

            // Isi data berdasarkan MK sebagai baris dan CPL sebagai kolom
            $row = 2;
            foreach ($mks as $index => $mk) {
                $sheet->setCellValue('A' . $row, $index + 1); // No
                $sheet->setCellValue('B' . $row, $mk->kode_mk ?? 'MK' . $mk->id); // Kode MK
                $colIndex = 3; // Mulai dari kolom C
                foreach ($cpls as $cpl) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                    $sheet->setCellValue($colLetter . $row, isset($pemetaan[$mk->id . '-' . $cpl->id]) ? 'V' : '');
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
            $tempFile = tempnam(sys_get_temp_dir(), 'cpl_mk_template');
            $writer->save($tempFile);

            Log::info("Template CPL-MK downloaded for kode_prodi: {$kodeProdi} with {$cplCount} CPL columns and {$mkCount} MK rows");
            return response()->download($tempFile, 'template_cpl_mk.xlsx')->deleteFileAfterSend(true);
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

            // Proses impor file menggunakan CplMkImport
            Excel::import(new CplMkImport($kodeProdi), $request->file('file'));

            // Berikan pesan sukses
            return redirect()->back()->with('success', 'Data pemetaan CPL-MK berhasil diimpor.');
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}