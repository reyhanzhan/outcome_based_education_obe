<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Pl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Imports\CplPlImport;

class Cpl_PlController extends Controller
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
            $pls = Pl::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpl_pl')
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->whereIn('pl_id', $pls->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpl_id . '-' . $item->pl_id => true];
                });

            Log::info("Successfully loaded pemetaan CPL-PL for kode_prodi: {$kodeProdi}, CPL count: {$cpls->count()}, PL count: {$pls->count()}");

            return view('pemetaan_CPL-PL.index', compact('cpls', 'pls', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPL-PL: ' . $e->getMessage());
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
                'pl_id' => 'required|exists:pl,id',
                'checked' => 'required|boolean',
            ]);

            $cpl = Cpl::where('id', $request->cpl_id)->where('kode_prodi', $kodeProdi)->first();
            $pl = Pl::where('id', $request->pl_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$cpl || !$pl) {
                return response()->json(['error' => 'CPL atau PL tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            // Langsung gunakan nilai checked setelah validasi
            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpl_pl')->updateOrInsert(
                    [
                        'cpl_id' => $request->cpl_id,
                        'pl_id' => $request->pl_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                Log::info("Pemetaan CPL-PL tersimpan: CPL ID {$request->cpl_id} - PL ID {$request->pl_id}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpl_pl')
                    ->where('cpl_id', $request->cpl_id)
                    ->where('pl_id', $request->pl_id)
                    ->delete();
                Log::info("Pemetaan CPL-PL dihapus: CPL ID {$request->cpl_id} - PL ID {$request->pl_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPL-PL: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
        }
    }

public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header utama
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Kode CPL');
            $kodeProdi = Auth::user()->kode_prodi;
            $pls = Pl::where('kode_prodi', $kodeProdi)->get();
            $sheet->mergeCells('C1:' . chr(66 + count($pls)) . '1');
            $sheet->setCellValue('C1', 'PL');

            // Set header PL di baris kedua
            $col = 'C';
            foreach ($pls as $pl) {
                $sheet->setCellValue($col . '2', $pl->kode_pl);
                $col++;
            }

            // Set header style
            $sheet->getStyle('A1:' . chr(66 + count($pls)) . '1')->getFont()->setBold(true);
            $sheet->getStyle('A1:' . chr(66 + count($pls)) . '1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A2:' . chr(66 + count($pls)) . '2')->getFont()->setBold(true);
            $sheet->getStyle('A2:' . chr(66 + count($pls)) . '2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Ambil data CPL dan PL dari database
            $cpls = Cpl::where('kode_prodi', $kodeProdi)->get();
            $pemetaan = DB::table('cpl_pl')
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->whereIn('pl_id', $pls->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpl_id . '-' . $item->pl_id => true];
                });

            // Isi data berdasarkan kombinasi CPL dan PL
            $row = 3;
            foreach ($cpls as $index => $cpl) {
                $sheet->setCellValue('A' . $row, $index + 1); // No
                $sheet->setCellValue('B' . $row, $cpl->kode_cpl); // Kode CPL
                $col = 'C';
                foreach ($pls as $pl) {
                    $sheet->setCellValue($col . $row, isset($pemetaan[$cpl->id . '-' . $pl->id]) ? 'V' : '');
                    $col++;
                }
                $row++;
            }

            // Auto-size columns
            foreach (range('A', chr(66 + count($pls))) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Simpan file sementara
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'cpl_pl_template');
            $writer->save($tempFile);

            Log::info("Template CPL-PL downloaded for kode_prodi: {$kodeProdi}");
            return response()->download($tempFile, 'template_cpl_pl.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh template: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        try {
            // Validasi file
            $request->validate([
                'file' => 'required|file|mimes:xls,xlsx,csv|max:2048',
            ]);

            // Periksa apakah file ada dan valid
            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                Log::error('Invalid or missing file uploaded.');
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('cpl_pl.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            if (!$kodeProdi) {
                Log::error('Kode prodi tidak ditemukan untuk user: ' . Auth::user()->id);
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            // Log informasi impor
            Log::info('Starting CPL-PL import for kode_prodi: ' . $kodeProdi);

            // Impor file menggunakan CplPlImport
            Excel::import(new CplPlImport($kodeProdi), $file);

            Log::info('CPL-PL import completed successfully for kode_prodi: ' . $kodeProdi);
            return redirect()->route('Cpl_Pl.index')->with('success', 'Data pemetaan CPL-PL berhasil diimpor.');
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}